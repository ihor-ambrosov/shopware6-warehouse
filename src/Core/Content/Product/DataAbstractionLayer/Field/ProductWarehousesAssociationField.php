<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Product\DataAbstractionLayer\Field;

use Ambros\Warehouse\Core\Content\Product\Aggregate\ProductWarehouse\ProductWarehouseDefinition;
use Ambros\Warehouse\Core\Content\Product\DataAbstractionLayer\FieldSerializer\ProductWarehousesAssociationFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;

class ProductWarehousesAssociationField extends OneToManyAssociationField
{
    public const PRIORITY = 90;

    public function __construct() {
        parent::__construct(
            'productWarehouses',
            ProductWarehouseDefinition::class,
            'product_id',
            'id'
        );
        $this->addFlags(new CascadeDelete());
    }
    
    public function getWarehouseIdField(): string
    {
        return 'warehouse_id';
    }

    public function getExtractPriority(): int
    {
        return self::PRIORITY;
    }
    
    protected function getSerializerClass(): string
    {
        return ProductWarehousesAssociationFieldSerializer::class;
    }
}
