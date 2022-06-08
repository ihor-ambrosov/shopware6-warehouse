<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Warehouse;

use Ambros\Warehouse\Core\Content\Warehouse\WarehouseEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class WarehouseTranslationEntity extends TranslationEntity
{
    /**
     * @var string
     */
    protected $warehouseId;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var WarehouseEntity|null
     */
    protected ?WarehouseEntity $warehouse;

    public function getWarehouseId(): string
    {
        return $this->warehouseId;
    }

    public function setWarehouseId(string $warehouseId): void
    {
        $this->warehouseId = $warehouseId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
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
