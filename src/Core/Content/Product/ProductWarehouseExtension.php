<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Product;

use Ambros\Warehouse\Core\Content\Product\DataAbstractionLayer\Field\ProductWarehousesAssociationField;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;

class ProductWarehouseExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        // $this->updateStockField($collection);
        $collection->add(new ProductWarehousesAssociationField());
    }

    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }

    private function getStockField(FieldCollection $collection): ?Field
    {
        $filteredCollection = \array_filter(\iterator_to_array($collection), function ($field) {
            return $field->getPropertyName() === 'stock';
        });
        return \count($filteredCollection) ? \current($filteredCollection) : null;
    }

    private function updateStockField(FieldCollection $collection): void
    {
        $field = $this->getStockField($collection);
        if ($field === null) {
            return;
        }
        if ($field->is(Required)) {
            $field->removeFlag(Required);
        }
    }
}