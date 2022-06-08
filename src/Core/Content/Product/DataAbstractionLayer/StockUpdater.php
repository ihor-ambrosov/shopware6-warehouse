<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Product\DataAbstractionLayer;

use Doctrine\DBAL\Connection;
use Ambros\Warehouse\Defaults as WarehouseDefaults;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Content\Product\DataAbstractionLayer\StockUpdater as ParentStockUpdater;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\StateMachine\Event\StateMachineTransitionEvent;

class StockUpdater extends ParentStockUpdater
{
    /**
     * @var ParentStockUpdater
     */
    private ParentStockUpdater $decorated;

    /**
     * @var Connection
     */
    private Connection $connection;

    public function __construct(
        ParentStockUpdater $decorated,
        Connection $connection
    ) {
        $this->decorated = $decorated;
        $this->connection = $connection;
    }
    
    public function getDecorated(): ParentStockUpdater
    {
        return $this->decorated;
    }

    public static function getSubscribedEvents()
    {
        return ParentStockUpdater::getSubscribedEvents();
    }

    public function triggerChangeSet(PreWriteValidationEvent $event): void
    {
        $this->getDecorated()->triggerChangeSet($event);
    }

    public function lineItemWritten(EntityWrittenEvent $event): void
    {
        $productIds = [];
        foreach ($event->getWriteResults() as $result) {
            if ($result->hasPayload('referencedId') && $result->getProperty('type') === LineItem::PRODUCT_LINE_ITEM_TYPE) {
                $productIds[] = $result->getProperty('referencedId');
            }
            if ($result->getOperation() === EntityWriteResult::OPERATION_INSERT) {
                continue;
            }
            $changeSet = $result->getChangeSet();
            if (!$changeSet) {
                continue;
            }
            $type = $changeSet->getBefore('type');
            if ($type !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
                continue;
            }
            if (!$changeSet->hasChanged('referenced_id') && !$changeSet->hasChanged('quantity')) {
                continue;
            }
            $productIds[] = $changeSet->getBefore('referenced_id');
            $productIds[] = $changeSet->getAfter('referenced_id');
        }
        $productIds = array_filter(array_unique($productIds));
        if (empty($productIds)) {
            return;
        }
        $this->update($productIds, $event->getContext());
    }

    public function stateChanged(StateMachineTransitionEvent $event): void
    {
        $this->getDecorated()->stateChanged($event);
        if ($event->getContext()->getVersionId() !== Defaults::LIVE_VERSION) {
            return;
        }
        if ($event->getEntityName() !== 'order') {
            return;
        }
        if ($event->getToPlace()->getTechnicalName() === OrderStates::STATE_COMPLETED) {
            $this->decreaseStock($event);
            return;
        }
        if ($event->getFromPlace()->getTechnicalName() === OrderStates::STATE_COMPLETED) {
            $this->increaseStock($event);
            return;
        }
        if (
            $event->getToPlace()->getTechnicalName() === OrderStates::STATE_CANCELLED || 
            $event->getFromPlace()->getTechnicalName() === OrderStates::STATE_CANCELLED
        ) {
            $quantitiesGroupedByProductIdAndWarehouseId = $this->getQuantitiesGroupedByProductIdAndWarehouseId($event->getEntityId());
            $productIds = \array_keys($quantitiesGroupedByProductIdAndWarehouseId);
            $this->updateAvailableStockAndSales($productIds, $event->getContext());
            $this->updateAvailable($productIds, $event->getContext());
            return;
        }
    }

    public function update(array $productIds, Context $context): void
    {
        $this->getDecorated()->update($productIds, $context);
        if ($context->getVersionId() !== Defaults::LIVE_VERSION) {
            return;
        }
        $this->updateAvailableStockAndSales($productIds, $context);
        $this->updateAvailable($productIds, $context);
    }

    public function orderPlaced(CheckoutOrderPlacedEvent $event): void
    {
        $productIds = [];
        foreach ($event->getOrder()->getLineItems() as $lineItem) {
            if ($lineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
                continue;
            }
            $productIds[] = $lineItem->getReferencedId();
        }
        $this->update($productIds, $event->getContext());
    }
    
    private function retryableQuery(\Closure $closure)
    {
        if (\Ambros\Warehouse\Version::isVersionGreaterOrEqual('6.4.5.0')) {
            RetryableQuery::retryable($this->connection, $closure);
        } else {
            RetryableQuery::retryable($closure);
        }
    }

    private function increaseStock(StateMachineTransitionEvent $event): void
    {
        $quantitiesGroupedByProductIdAndWarehouseId = $this->getQuantitiesGroupedByProductIdAndWarehouseId($event->getEntityId());
        $productIds = \array_keys($quantitiesGroupedByProductIdAndWarehouseId);
        $this->updateStock($quantitiesGroupedByProductIdAndWarehouseId, +1);
        $this->updateAvailableStockAndSales($productIds, $event->getContext());
        $this->updateAvailable($productIds, $event->getContext());
    }

    private function decreaseStock(StateMachineTransitionEvent $event): void
    {
        $quantitiesGroupedByProductIdAndWarehouseId = $this->getQuantitiesGroupedByProductIdAndWarehouseId($event->getEntityId());
        $productIds = \array_keys($quantitiesGroupedByProductIdAndWarehouseId);
        $this->updateStock($quantitiesGroupedByProductIdAndWarehouseId, -1);
        $this->updateAvailableStockAndSales($productIds, $event->getContext());
        $this->updateAvailable($productIds, $event->getContext());
    }
    
    private function updateStock(array $quantitiesGroupedByProductIdAndWarehouseId, int $multiplier): void
    {
        foreach ($quantitiesGroupedByProductIdAndWarehouseId as $productId => $quantitiesGroupedByWarehouseId) {
            foreach ($quantitiesGroupedByWarehouseId as $warehouseId => $quantity) {
                $this->retryableQuery(function () use ($quantity, $multiplier, $productId, $warehouseId): void {
                    $this->connection->executeUpdate(
                        'UPDATE product_warehouse SET stock = stock + :quantity WHERE '.
                            'product_id = :productId AND product_version_id = :versionId AND warehouse_id = :warehouseId',
                        [
                            'quantity' => $quantity * $multiplier,
                            'productId' => Uuid::fromHexToBytes($productId),
                            'versionId' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION),
                            'warehouseId' => Uuid::fromHexToBytes($warehouseId),
                        ]
                    );
                });
            }
        }
    }

    private function updateAvailableStockAndSales(array $productIds, Context $context): void
    {
        $productIds = \array_filter(\array_unique($productIds));
        if (empty($productIds)) {
            return;
        }
        $warehouseIdsGroupedByProductId = $this->getWarehouseIdsGroupedByProductId($productIds, $context);
        $quantitiesGroupedByProductIdAndWarehouseId = $this->getOpenAndSalesQuantitiesGroupedByProductIdAndWarehouseId($productIds, $context);
        foreach ($warehouseIdsGroupedByProductId as $productId => $warehouseIds) {
            foreach ($warehouseIds as $warehouseId) {
                $this->retryableQuery(function () use ($quantitiesGroupedByProductIdAndWarehouseId, $productId, $warehouseId): void {
                    $this->connection->executeUpdate(
                        'UPDATE product_warehouse SET available_stock = stock - :openQuantity, sales = :salesQuantity, updated_at = :now WHERE '.
                            'product_id = :productId AND warehouse_id = :warehouseId',
                        [
                            'productId' => Uuid::fromHexToBytes($productId),
                            'warehouseId' => Uuid::fromHexToBytes($warehouseId),
                            'openQuantity' => $quantitiesGroupedByProductIdAndWarehouseId[$productId][$warehouseId]['open_quantity'] ?? 0,
                            'salesQuantity' => $quantitiesGroupedByProductIdAndWarehouseId[$productId][$warehouseId]['sales_quantity'] ?? 0,
                            'now' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                        ]
                    );
                });
            }
        }
    }

    private function updateAvailable(array $productIds, Context $context): void
    {
        $this->retryableQuery(function () use ($context, $productIds): void {
            $isCloseout = 'IFNULL(product.is_closeout, parent.is_closeout)';
            $availableStock = 'product_warehouse.available_stock';
            $minPurchase = 'IFNULL(product.min_purchase, parent.min_purchase)';
            $this->connection->executeUpdate(
                'UPDATE product_warehouse 
                    LEFT JOIN product ON product.id = product_warehouse.product_id AND product.version_id = product_warehouse.product_version_id
                    LEFT JOIN product parent ON parent.id = product.parent_id AND parent.version_id = product.version_id
                    SET product_warehouse.available = IFNULL(('.$isCloseout.' * '.$availableStock.' >= '.$isCloseout.' * '.$minPurchase.'), 0)
                    WHERE product_warehouse.product_id IN (:productIds) AND product.version_id = :versionId',
                [
                    'productIds' => Uuid::fromHexToBytesList($productIds),
                    'versionId' => Uuid::fromHexToBytes($context->getVersionId())
                ],
                [
                    'productIds' => Connection::PARAM_STR_ARRAY
                ]
            );
        });
    }

    private function getOpenAndSalesQuantitiesGroupedByProductIdAndWarehouseId(array $productIds, Context $context): array
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $orderTable = $this->connection->quoteIdentifier('order');
        $orderTableAlias = $this->connection->quoteIdentifier('order');
        $queryBuilder->select(
            'order_line_item.product_id AS product_id',
            $orderTableAlias.'.warehouse_id AS warehouse_id',
            'IFNULL(SUM(IF(state_machine_state.technical_name = :completedState, 0, order_line_item.quantity)), 0) AS open_quantity',
            'IFNULL(SUM(IF(state_machine_state.technical_name = :completedState, order_line_item.quantity, 0)), 0) AS sales_quantity'
        );
        $queryBuilder->from('order_line_item', 'order_line_item');
        $queryBuilder->join(
            'order_line_item',
            $orderTable,
            $orderTableAlias,
            $orderTableAlias.'.id = order_line_item.order_id AND '.$orderTableAlias.'.version_id = order_line_item.order_version_id'
        );
        $queryBuilder->join(
            'order_line_item',
            'state_machine_state',
            'state_machine_state',
            'state_machine_state.id = '.$orderTableAlias.'.state_id AND state_machine_state.technical_name <> :cancelledState'
        );
        $queryBuilder->andWhere('order_line_item.product_id IN (:productIds)');
        $queryBuilder->andWhere('order_line_item.version_id = :versionId');
        $queryBuilder->andWhere('order_line_item.type = :type');
        $queryBuilder->andWhere('order_line_item.product_id IS NOT NULL');
        $queryBuilder->groupBy('order_line_item.product_id', $orderTableAlias.'.warehouse_id');
        $queryBuilder->setParameter('completedState', OrderStates::STATE_COMPLETED);
        $queryBuilder->setParameter('cancelledState', OrderStates::STATE_CANCELLED);
        $queryBuilder->setParameter('productIds', Uuid::fromHexToBytesList($productIds), Connection::PARAM_STR_ARRAY);
        $queryBuilder->setParameter('versionId', Uuid::fromHexToBytes($context->getVersionId()));
        $queryBuilder->setParameter('type', LineItem::PRODUCT_LINE_ITEM_TYPE);
        $orderLineItems = $queryBuilder->execute()->fetchAllAssociative();
        $quantitiesGroupedByProductIdAndWarehouseId = [];
        foreach ($orderLineItems as $orderLineItem) {
            $productId = Uuid::fromBytesToHex($orderLineItem['product_id']);
            $warehouseId = !empty($orderLineItem['warehouse_id']) ? Uuid::fromBytesToHex($orderLineItem['warehouse_id']) : WarehouseDefaults::WAREHOUSE_ID;
            $quantitiesGroupedByProductIdAndWarehouseId[$productId][$warehouseId] = [
                'open_quantity' => $orderLineItem['open_quantity'],
                'sales_quantity' => $orderLineItem['sales_quantity']
            ];
        }
        return $quantitiesGroupedByProductIdAndWarehouseId;
    }

    private function getWarehouseIdsGroupedByProductId(array $productIds, Context $context): array
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('product_id', 'warehouse_id');
        $queryBuilder->from('product_warehouse');
        $queryBuilder->andWhere('product_id IN (:productIds)');
        $queryBuilder->andWhere('product_version_id = :versionId');
        $queryBuilder->setParameter('productIds', Uuid::fromHexToBytesList($productIds), Connection::PARAM_STR_ARRAY);
        $queryBuilder->setParameter('versionId', Uuid::fromHexToBytes($context->getVersionId()));
        $warehouseIdsGroupedByProductId = [];
        foreach ($queryBuilder->execute()->fetchAllAssociative() as $productWarehouse) {
            $productId = Uuid::fromBytesToHex($productWarehouse['product_id']);
            $warehouseId = Uuid::fromBytesToHex($productWarehouse['warehouse_id']);
            $warehouseIdsGroupedByProductId[$productId][$warehouseId] = $warehouseId;
        }
        return $warehouseIdsGroupedByProductId;
    }
    
    private function getQuantitiesGroupedByProductIdAndWarehouseId(string $orderId): array
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $orderTable = $this->connection->quoteIdentifier('order');
        $orderTableAlias = $this->connection->quoteIdentifier('order');
        $queryBuilder->select(
            'order_line_item.referenced_id AS product_id',
            'order_line_item.quantity AS quantity',
            $orderTableAlias.'.warehouse_id AS warehouse_id'
        );
        $queryBuilder->from('order_line_item', 'order_line_item');
        $queryBuilder->join(
            'order_line_item',
            $orderTable,
            $orderTableAlias,
            $orderTableAlias.'.id = order_line_item.order_id AND '.$orderTableAlias.'.version_id = order_line_item.order_version_id'
        );
        $queryBuilder->andWhere('order_line_item.type = :type');
        $queryBuilder->andWhere('order_line_item.order_id = :orderId');
        $queryBuilder->andWhere('order_line_item.version_id = :versionId');
        $queryBuilder->setParameter('orderId', Uuid::fromHexToBytes($orderId));
        $queryBuilder->setParameter('versionId', Uuid::fromHexToBytes(Defaults::LIVE_VERSION));
        $queryBuilder->setParameter('type', LineItem::PRODUCT_LINE_ITEM_TYPE);
        $quantitiesGroupedByProductIdAndWarehouseId = [];
        foreach ($queryBuilder->execute()->fetchAllAssociative() as $orderLineItem) {
            $productId = (string) $orderLineItem['product_id'];
            $warehouseId = !empty($orderLineItem['warehouse_id']) ? Uuid::fromBytesToHex($orderLineItem['warehouse_id']) : WarehouseDefaults::WAREHOUSE_ID;
            $quantity = (int) $orderLineItem['quantity'];
            $quantitiesGroupedByProductIdAndWarehouseId[$productId][$warehouseId] = $quantity;
        }
        return $quantitiesGroupedByProductIdAndWarehouseId;
    }
}