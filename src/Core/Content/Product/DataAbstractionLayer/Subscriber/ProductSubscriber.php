<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Product\DataAbstractionLayer\Subscriber;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductSubscriber implements EventSubscriberInterface
{
    /**
     * @var Connection
     */
    private Connection $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_WRITTEN_EVENT => ['onWritten', -1000]
        ];
    }

    public function onWritten(EntityWrittenEvent $event)
    {
        $this->updateStock($event->getIds(), $event->getContext());
    }
    
    private function retryableQuery(\Closure $closure)
    {
        if (\Ambros\Warehouse\Version::isVersionGreaterOrEqual('6.4.5.0')) {
            RetryableQuery::retryable($this->connection, $closure);
        } else {
            RetryableQuery::retryable($closure);
        }
    }

    private function getStockGroupedByProductIds(array $productIds, Context $context)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select(
            'product_warehouse.product_id AS product_id',
            'SUM(product_warehouse.stock) AS stock'
        );
        $queryBuilder->from('product_warehouse', 'product_warehouse');
        $queryBuilder->andWhere('product_warehouse.product_id IN (:productIds)');
        $queryBuilder->andWhere('product_warehouse.product_version_id = :versionId');
        $queryBuilder->groupBy('product_warehouse.product_id');
        $queryBuilder->setParameter('productIds', Uuid::fromHexToBytesList($productIds), Connection::PARAM_STR_ARRAY);
        $queryBuilder->setParameter('versionId', Uuid::fromHexToBytes($context->getVersionId()));
        $stockGroupedByProductIds = [];
        foreach ($queryBuilder->execute()->fetchAllAssociative() as $product) {
            $productId = Uuid::fromBytesToHex($product['product_id']);
            $stockGroupedByProductIds[$productId] = (int) $product['stock'];
        }
        return $stockGroupedByProductIds;
    }

    private function updateStock(array $productIds, Context $context): void
    {
        $stockGroupedByProductIds = $this->getStockGroupedByProductIds($productIds, $context);
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->update('product', 'product');
        $queryBuilder->set('product.stock', ':stock');
        $queryBuilder->andWhere('product.id = :productId');
        $queryBuilder->andWhere('product.version_id = :versionId');
        $queryBuilder->setParameter('versionId', Uuid::fromHexToBytes($context->getVersionId()));
        foreach ($productIds as $productId) {
            $stock = $stockGroupedByProductIds[$productId] ?? 0;
            $this->retryableQuery(function () use ($queryBuilder, $productId, $stock): void {
                $queryBuilder->setParameter('productId', Uuid::fromHexToBytes($productId));
                $queryBuilder->setParameter('stock', $stock);
                $queryBuilder->execute();
            });
        }
    }
}