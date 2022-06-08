<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Warehouse;

use Ambros\Warehouse\Core\Content\Product\Aggregate\ProductWarehouse\ProductWarehouseCollection;
use Ambros\Warehouse\Core\Content\Warehouse\WarehouseTranslationCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class WarehouseEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var int
     */
    protected $priority;
    
    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var WarehouseTranslationCollection|null
     */
    protected $translations;
    
    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }
    
    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getTranslations(): ?WarehouseTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(WarehouseTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getProductWarehouses(): ?ProductWarehouseCollection
    {
        return $this->productWarehouses;
    }

    public function setProductWarehouses(ProductWarehouseCollection $productWarehouses): void
    {
        $this->productWarehouses = $productWarehouses;
    }
}
