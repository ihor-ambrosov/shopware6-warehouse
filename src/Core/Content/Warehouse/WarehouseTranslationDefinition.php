<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Warehouse;

use Ambros\Warehouse\Core\Content\Warehouse\WarehouseDefinition;
use Ambros\Warehouse\Core\Content\Warehouse\WarehouseTranslationCollection;
use Ambros\Warehouse\Core\Content\Warehouse\WarehouseTranslationEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;

class WarehouseTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'warehouse_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return WarehouseTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return WarehouseTranslationEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function getParentDefinitionClass(): string
    {
        return WarehouseDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))
                ->addFlags(new ApiAware(), new Required()),
        ]);
    }
}
