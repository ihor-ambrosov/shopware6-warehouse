<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Product\DataAbstractionLayer\FieldSerializer;

use Ambros\Warehouse\Core\Content\Product\DataAbstractionLayer\Field\ProductWarehousesAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\DecodeByHydratorException;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InvalidSerializerFieldException;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\MissingSystemTranslationException;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\MissingTranslationLanguageException;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\FieldSerializerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\FieldException\ExpectedArrayException;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteCommandExtractor;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;

class ProductWarehousesAssociationFieldSerializer implements FieldSerializerInterface
{
    /**
     * @var WriteCommandExtractor
     */
    protected WriteCommandExtractor $writeExtractor;

    public function __construct(
        WriteCommandExtractor $writeExtractor
    ) {
        $this->writeExtractor = $writeExtractor;
    }
    
    public function normalize(Field $field, array $data, WriteParameterBag $parameters): array
    {
        $this->validateField($field);
        $propertyName = $field->getPropertyName();
        $productWarehouses = $data[$propertyName] ?? null;
        if ($productWarehouses === null) {
            return $data;
        }
        $definition = $parameters->getDefinition();
        $referenceDefinition = $field->getReferenceDefinition();
        $referenceFields = $referenceDefinition->getFields();
        $referenceParameters = $parameters->cloneForSubresource($referenceDefinition, $parameters->getPath().'/'.$propertyName);
        $currentProductId = $parameters->getContext()->get($definition->getClass(), $field->getLocalField());
        $productIdField = $referenceFields->getByStorageName($field->getReferenceField());
        $productIdPropertyName = $productIdField->getPropertyName();
        $productVersionIdField = $referenceFields->getByStorageName($definition->getEntityName().'_version_id');
        $warehouseIdField = $referenceFields->getByStorageName($field->getWarehouseIdField());
        $warehouseIdPropertyName = $warehouseIdField->getPropertyName();
        foreach ($productWarehouses as $index => &$productWarehouse) {
            if (!\is_array($productWarehouse)) {
                throw new ExpectedArrayException($parameters->getPath().'/'.$propertyName);
            }
            $productWarehouse[$productIdPropertyName] = $this->getProductId($productWarehouse, $productIdPropertyName, $currentProductId);
            $productWarehouse[$warehouseIdPropertyName] = $this->getWarehouseId($productWarehouse, $warehouseIdPropertyName, $index);
            $productWarehouse = $this->writeExtractor->normalizeSingle(
                $referenceDefinition,
                $productVersionIdField->getSerializer()->normalize($productVersionIdField, $productWarehouse, $referenceParameters),
                $referenceParameters
            );
        }
        $data[$propertyName] = $productWarehouses;
        return $data;
    }

    /**
     * @throws ExpectedArrayException
     * @throws InvalidSerializerFieldException
     * @throws MissingSystemTranslationException
     * @throws MissingTranslationLanguageException
     */
    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        $this->validateField($field);
        $productWarehouses = $data->getValue();
        if ($productWarehouses === null) {
            yield from [];
            return;
        }
        if (!\is_array($productWarehouses)) {
            throw new ExpectedArrayException($parameters->getPath().'/'.$data->getKey());
        }
        $this->map($field, $parameters, $data);
        yield from [];
    }

    public function decode(Field $field, $value): void
    {
        throw new DecodeByHydratorException($field);
    }
    
    private function validateField(Field $field)
    {
        if (!$field instanceof ProductWarehousesAssociationField) {
            throw new InvalidSerializerFieldException(ProductWarehousesAssociationField::class, $field);
        }
    }
    
    private function getProductId(array $productWarehouse, string $productIdPropertyName, $currentProductId)
    {
        if (\array_key_exists($productIdPropertyName, $productWarehouse) && $productWarehouse[$productIdPropertyName] === null) {
            return null;
        }
        return $currentProductId;
    }
    
    private function getWarehouseId(array $productWarehouse, string $warehouseIdPropertyName, $index)
    {
        $warehouseId = !empty($productWarehouse[$warehouseIdPropertyName])? $productWarehouse[$warehouseIdPropertyName] : null;
        if ($warehouseId === null && !\is_numeric($index)) {
            $warehouseId = $index;
        }
        if ($warehouseId === null && !empty($productWarehouse['id'])) {
            $warehouseId = \explode('-', $productWarehouse['id'])[1] ?? null;
        }
        return $warehouseId;
    }
    
    private function map(OneToManyAssociationField $field, WriteParameterBag $parameters, KeyValuePair $data): void
    {
        $referenceDefinition = $field->getReferenceDefinition();
        foreach ($data->getValue() as $warehouseId => $productWarehouse) {
            $this->writeExtractor->extract(
                $productWarehouse,
                $parameters->cloneForSubresource($referenceDefinition, $parameters->getPath().'/'.$data->getKey().'/'.$warehouseId)
            );
        }
    }
}