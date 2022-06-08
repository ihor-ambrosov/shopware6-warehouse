<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Warehouse;

use Ambros\Warehouse\Core\Content\Warehouse\WarehouseEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(WarehouseEntity $entity)
 * @method void set(string $key, WarehouseEntity $entity)
 * @method WarehouseEntity[] getIterator()
 * @method WarehouseEntity[] getElements()
 * @method WarehouseEntity|null get(string $key)
 * @method WarehouseEntity|null first()
 * @method WarehouseEntity|null last()
 */
class WarehouseCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'warehouse_collection';
    }

    protected function getExpectedClass(): string
    {
        return WarehouseEntity::class;
    }
}
