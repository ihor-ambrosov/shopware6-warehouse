<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Product\Aggregate\ProductWarehouse;

use Ambros\Warehouse\Core\Content\Warehouse\WarehouseEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class ProductWarehouseEntity extends Entity
{
    /**
     * @var string
     */
    protected $productId;

    /**
     * @var string
     */
    protected $warehouseId;
    
    /**
     * @var int
     */
    protected $stock;

    /**
     * @var int|null
     */
    protected $availableStock;

    /**
     * @var bool
     */
    protected $available;
    
    /**
     * @var int
     */
    protected $sales;

    /**
     * @var ProductEntity|null
     */
    protected ?ProductEntity $product;

    /**
     * @var WarehouseEntity|null
     */
    protected ?WarehouseEntity $warehouse;

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    public function getWarehouseId(): string
    {
        return $this->warehouseId;
    }

    public function setWarehouseId(string $warehouseId): void
    {
        $this->warehouseId = $warehouseId;
    }
    
    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }
    
    public function getAvailableStock(): ?int
    {
        return $this->availableStock;
    }

    public function setAvailableStock(int $availableStock): void
    {
        $this->availableStock = $availableStock;
    }

    public function getAvailable(): bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): void
    {
        $this->available = $available;
    }
    
    public function getSales(): int
    {
        return $this->sales;
    }

    public function setSales(int $sales): void
    {
        $this->sales = $sales;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getWarehouse(): ?WarehouseEntity
    {
        return $this->warehouse;
    }

    public function setWarehouse(WarehouseEntity $warehouse): void
    {
        $this->warehouse = $warehouse;
    }
}