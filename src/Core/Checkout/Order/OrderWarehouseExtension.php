<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Checkout\Order;

use Ambros\Warehouse\Core\Content\Warehouse\WarehouseDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;

class OrderWarehouseExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(new FkField('warehouse_id', 'warehouseId', WarehouseDefinition::class));
        $collection->add(new ManyToOneAssociationField('warehouse', 'warehouse_id', WarehouseDefinition::class, 'id', false));
    }

    public function getDefinitionClass(): string
    {
        return OrderDefinition::class;
    }
}