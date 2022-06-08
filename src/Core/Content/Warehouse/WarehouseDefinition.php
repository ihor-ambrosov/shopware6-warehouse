<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Warehouse;

use Ambros\Warehouse\Core\Content\Product\Aggregate\ProductWarehouse\ProductWarehouseDefinition;
use Ambros\Warehouse\Core\Content\Warehouse\DataAbstractionLayer\Field\WarehouseCodeField;
use Ambros\Warehouse\Core\Content\Warehouse\WarehouseCollection;
use Ambros\Warehouse\Core\Content\Warehouse\WarehouseEntity;
use Ambros\Warehouse\Core\Content\Warehouse\WarehouseTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;

class WarehouseDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'warehouse';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return WarehouseCollection::class;
    }

    public function getEntityClass(): string
    {
        return WarehouseEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))
                ->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new WarehouseCodeField()),
            (new IntField('priority', 'priority'))
                ->addFlags(new ApiAware(), new Required()),
            (new TranslatedField('name'))
                ->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslationsAssociationField(WarehouseTranslationDefinition::class, 'warehouse_id'))
                ->addFlags(new ApiAware(), new Required()),
            (new OneToManyAssociationField('productWarehouses', ProductWarehouseDefinition::class, 'warehouse_id'))
                ->addFlags(new CascadeDelete()),
        ]);
    }
}