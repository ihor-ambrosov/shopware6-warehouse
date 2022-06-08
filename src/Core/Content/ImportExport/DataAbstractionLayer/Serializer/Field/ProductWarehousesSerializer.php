<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\ImportExport\DataAbstractionLayer\Serializer\Field;

use Ambros\Warehouse\Core\Content\Product\Aggregate\ProductWarehouse\ProductWarehouseEntity;
use Ambros\Warehouse\Core\Content\Product\DataAbstractionLayer\Field\ProductWarehousesAssociationField;
use Shopware\Core\Content\ImportExport\DataAbstractionLayer\Serializer\Field\FieldSerializer;
use Shopware\Core\Content\ImportExport\Struct\Config;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProductWarehousesSerializer extends FieldSerializer
{
    /**
     * @var EntityRepositoryInterface
     */
    private EntityRepositoryInterface $warehouseRepository;

    public function __construct(EntityRepositoryInterface $warehouseRepository)
    {
        $this->warehouseRepository = $warehouseRepository;
    }
    
    public function serialize(Config $config, Field $field, $productWarehouses): iterable
    {
        if ($productWarehouses === null) {
            return;
        }
        $this->validateField($field);
        if ($productWarehouses instanceof EntityCollection) {
            $productWarehouses = $productWarehouses->jsonSerialize();
        }
        $referenceDefinition = $field->getReferenceDefinition();
        $entitySerializer = $this->serializerRegistry->getEntity($referenceDefinition->getEntityName());
        $serializedProductWarehouses = [];
        foreach ($productWarehouses as $warehouseId => $productWarehouse) {
            if ($productWarehouse instanceof ProductWarehouseEntity) {
                $warehouseId = $productWarehouse->getWarehouseId();
            }
            $code = $this->getCodeByWarehouseId($warehouseId);
            $serializedProductWarehouse = $entitySerializer->serialize($config, $referenceDefinition, $productWarehouse);
            if (!\is_array($serializedProductWarehouse) && \is_iterable($serializedProductWarehouse)) {
                $serializedProductWarehouse = \iterator_to_array($serializedProductWarehouse);
            }
            $serializedProductWarehouses[$code] = $serializedProductWarehouse;
        }
        yield $field->getPropertyName() => $serializedProductWarehouses;
    }

    public function deserialize(Config $config, Field $field, $productWarehouses)
    {
        $this->validateField($field);
        if (empty($productWarehouses)) {
            return null;
        }
        if (!\is_array($productWarehouses) && \is_iterable($productWarehouses)) {
            $productWarehouses = \iterator_to_array($productWarehouses);
        }
        $referenceDefinition = $field->getReferenceDefinition();
        $entitySerializer = $this->serializerRegistry->getEntity($referenceDefinition->getEntityName());
        $deserializedProductWarehouses = [];
        foreach ($productWarehouses as $code => $productWarehouse) {
            $warehouseId = $this->getWarehouseIdByCode($code);
            $deserializedProductWarehouse = $entitySerializer->deserialize($config, $referenceDefinition, $productWarehouse);
            if (!\is_array($deserializedProductWarehouse) && \is_iterable($deserializedProductWarehouse)) {
                $deserializedProductWarehouse = \iterator_to_array($deserializedProductWarehouse);
            }
            $deserializedProductWarehouses[$warehouseId] = $deserializedProductWarehouse;
        }
        if (empty($deserializedProductWarehouses)) {
            return null;
        }
        return $deserializedProductWarehouses;
    }

    public function supports(Field $field): bool
    {
        return $field instanceof ProductWarehousesAssociationField;
    }

    private function validateField(Field $field): void
    {
        if (!$field instanceof ProductWarehousesAssociationField) {
            throw new \InvalidArgumentException('Expected argument to be an instance of '.ProductWarehousesAssociationField::class);
        }
    }

    private function getCodeByWarehouseId(string $warehouseId): ?string
    {
        $criteria = (new Criteria([$warehouseId]));
        $warehouse = $this->warehouseRepository->search($criteria, Context::createDefaultContext())->first();
        return $warehouse ? $warehouse->getCode() : $warehouseId;
    }

    private function getWarehouseIdByCode(string $code): ?string
    {
        $criteria = (new Criteria())->addFilter(new EqualsFilter('code', $code));
        $warehouse = $this->warehouseRepository->search($criteria, Context::createDefaultContext())->first();
        return $warehouse === null ? null : $warehouse->getId();
    }
}