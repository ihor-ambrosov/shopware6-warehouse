<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Warehouse;

use Ambros\Warehouse\Core\Content\Warehouse\WarehouseTranslationEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(WarehouseTranslationEntity $entity)
 * @method void set(string $key, WarehouseTranslationEntity $entity)
 * @method WarehouseTranslationEntity[] getIterator()
 * @method WarehouseTranslationEntity[] getElements()
 * @method WarehouseTranslationEntity|null get(string $key)
 * @method WarehouseTranslationEntity|null first()
 * @method WarehouseTranslationEntity|null last()
 */
class WarehouseTranslationCollection extends EntityCollection
{
    public function getWarehouseIds(): array
    {
        return $this->fmap(function (WarehouseTranslationEntity $warehouseTranslation) {
            return $warehouseTranslation->getWarehouseId();
        });
    }

    public function filterByWarehouseId(string $warehouseId): self
    {
        return $this->filter(function (WarehouseTranslationEntity $warehouseTranslation) use ($warehouseId) {
            return $warehouseTranslation->getWarehouseId() === $warehouseId;
        });
    }

    public function getLanguageIds(): array
    {
        return $this->fmap(function (WarehouseTranslationEntity $warehouseTranslation) {
            return $warehouseTranslation->getLanguageId();
        });
    }

    public function filterByLanguageId(string $languageId): self
    {
        return $this->filter(function (WarehouseTranslationEntity $warehouseTranslation) use ($languageId) {
            return $warehouseTranslation->getLanguageId() === $languageId;
        });
    }

    public function getApiAlias(): string
    {
        return 'warehouse_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return WarehouseTranslationEntity::class;
    }
}
