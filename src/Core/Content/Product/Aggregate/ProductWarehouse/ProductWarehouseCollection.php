<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Product\Aggregate\ProductWarehouse;

use Ambros\Warehouse\Core\Content\Product\Aggregate\ProductWarehouse\ProductWarehouseEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ProductWarehouseCollection extends EntityCollection
{
    public function getProductIds(): array
    {
        return $this->fmap(function (ProductWarehouseEntity $productWarehouse) {
            return $productWarehouse->getProductId();
        });
    }

    public function filterByProductId(string $productId): self
    {
        return $this->filter(function (ProductWarehouseEntity $productWarehouse) use ($productId) {
            return $productWarehouse->getProductId() === $productId;
        });
    }
    
    public function getWarehouseIds(): array
    {
        return $this->fmap(function (ProductWarehouseEntity $productWarehouse) {
            return $productWarehouse->getWarehouseId();
        });
    }

    public function filterByWarehouseId(string $warehouseId): self
    {
        return $this->filter(function (ProductWarehouseEntity $productWarehouse) use ($warehouseId) {
            return $productWarehouse->getWarehouseId() === $warehouseId;
        });
    }

    public function getApiAlias(): string
    {
        return 'product_warehouse_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductWarehouseEntity::class;
    }
}