<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Product\Aggregate\ProductWarehouse;

use Ambros\Warehouse\Core\Content\Product\Aggregate\ProductWarehouse\ProductWarehouseCollection;
use Ambros\Warehouse\Core\Content\Product\Aggregate\ProductWarehouse\ProductWarehouseEntity;
use Ambros\Warehouse\Core\Content\Warehouse\WarehouseDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;

class ProductWarehouseDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'product_warehouse';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }
    
    public function isVersionAware(): bool
    {
        return true;
    }

    public function getEntityClass(): string
    {
        return ProductWarehouseEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ProductWarehouseCollection::class;
    }
    
    public function getDefaults(): array
    {
        return [
            'stock' => 0,
        ];
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function getParentDefinitionClass(): ?string
    {
        return ProductDefinition::class;
    }
    
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('product_id', 'productId', ProductDefinition::class))
                ->addFlags(new PrimaryKey(), new Required()),
            (new ReferenceVersionField(ProductDefinition::class))
                ->addFlags(new PrimaryKey(), new Required()),
            (new FkField('warehouse_id', 'warehouseId', WarehouseDefinition::class))
                ->addFlags(new PrimaryKey(), new Required()),
            (new IntField('stock', 'stock'))
                ->addFlags(new Required()),
            (new IntField('available_stock', 'availableStock'))
                ->addFlags(new WriteProtected()),
            (new BoolField('available', 'available'))
                ->addFlags(new WriteProtected()),
            (new IntField('sales', 'sales'))
                ->addFlags(new WriteProtected()),
            new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id', false),
            new ManyToOneAssociationField('warehouse', 'warehouse_id', WarehouseDefinition::class, 'id', false),
        ]);
    }
}