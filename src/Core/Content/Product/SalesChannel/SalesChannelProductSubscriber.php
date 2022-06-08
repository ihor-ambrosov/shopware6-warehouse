<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Product\SalesChannel;

use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntityLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ambros\Warehouse\Core\Framework\ContextWarehouseExtension;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Context;

class SalesChannelProductSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private EntityRepositoryInterface $productWarehouseRepository;

    public function __construct(
        EntityRepositoryInterface $productWarehouseRepository
    ) {
        $this->productWarehouseRepository = $productWarehouseRepository;
    }
    
    public static function getSubscribedEvents()
    {
        return [
            'sales_channel.product.loaded' => ['onLoaded', 1000],
        ];
    }

    public function onLoaded(SalesChannelEntityLoadedEvent $event): void
    {
        $products = $event->getEntities();
        $context = $event->getSalesChannelContext()->getContext();
        $warehouseId = $context->getExtension(ContextWarehouseExtension::KEY)->getWarehouseId();
        if (!$warehouseId) {
            return;
        }
        $productIds = \array_map(function (SalesChannelProductEntity $product) {
            return $product->getId();
        }, $products);
        $productWarehouses = $this->getProductWarehouses($productIds, $warehouseId, $context);
        foreach ($products as $product) {
            $productWarehouse = $productWarehouses[$product->getId()] ?? null;
            $product->setStock($productWarehouse ? $productWarehouse->getStock() : 0);
            $product->setAvailableStock($productWarehouse ? $productWarehouse->getAvailableStock() : 0);
            $product->setAvailable($productWarehouse ? $productWarehouse->getAvailable() : false);
            $product->setSales($productWarehouse ? $productWarehouse->getSales() : 0);
        }
    }
    
    private function getProductWarehouses(array $productIds, string $warehouseId, Context $context)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('productId', $productIds));
        $criteria->addFilter(new EqualsFilter('productVersionId', $context->getVersionId()));
        $criteria->addFilter(new EqualsFilter('warehouseId', $warehouseId));
        $productWarehouses = $this->productWarehouseRepository->search($criteria, $context);
        $groupedProductWarehouses = [];
        foreach ($productWarehouses as $productWarehouse) {
            $groupedProductWarehouses[$productWarehouse->getProductId()] = $productWarehouse;
        }
        return $groupedProductWarehouses;
    }
}