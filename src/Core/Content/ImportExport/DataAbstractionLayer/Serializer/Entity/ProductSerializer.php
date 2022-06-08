<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\ImportExport\DataAbstractionLayer\Serializer\Entity;

use Shopware\Core\Content\ImportExport\DataAbstractionLayer\Serializer\Entity\ProductSerializer as ParentProductSerializer;
use Shopware\Core\Content\ImportExport\DataAbstractionLayer\Serializer\SerializerRegistry;
use Shopware\Core\Content\ImportExport\Struct\Config;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\Struct\Struct;

class ProductSerializer extends ParentProductSerializer
{
    /**
     * @var ParentProductSerializer
     */
    private ParentProductSerializer $decorated;

    public function __construct(ParentProductSerializer $decorated) {
        $this->decorated = $decorated;
    }

    public function getDecorated(): ParentProductSerializer
    {
        return $this->decorated;
    }
    
    /**
     * @param array|Struct|null $entity
     */
    public function serialize(Config $config, EntityDefinition $definition, $entity): iterable
    {
        if ($entity instanceof Struct) {
            $entity = $entity->jsonSerialize();
        }
        yield from $this->getDecorated()->serialize($config, $definition, $entity);
        $productWarehousesField = $definition->getField('productWarehouses');
        $productWarehousesSerializer = $this->serializerRegistry->getFieldSerializer($productWarehousesField);
        $productWarehouses = $entity['extensions']['productWarehouses'] ?? null;
        yield from $productWarehousesSerializer->serialize($config, $productWarehousesField, $productWarehouses);
    }

    /**
     * @param array|\Traversable $entity
     * @return array|\Traversable
     */
    public function deserialize(Config $config, EntityDefinition $definition, $entity)
    {
        if (!\is_array($entity) && \is_iterable($entity)) {
            $entity = \iterator_to_array($entity);
        }
        yield from $this->getDecorated()->deserialize($config, $definition, $entity);
        $productWarehousesField = $definition->getField('productWarehouses');
        $productWarehousesSerializer = $this->serializerRegistry->getFieldSerializer($productWarehousesField);
        $productWarehouses = $entity['productWarehouses'] ?? null;
        $deserializedProductWarehouses = $productWarehousesSerializer->deserialize($config, $productWarehousesField, $productWarehouses);
        if ($deserializedProductWarehouses === null) {
            return;
        }
        if (\is_iterable($deserializedProductWarehouses) && !\is_array($deserializedProductWarehouses)) {
            $deserializedProductWarehouses = \iterator_to_array($deserializedProductWarehouses);
        }
        yield 'productWarehouses' => $deserializedProductWarehouses;
    }

    public function supports(string $entity): bool
    {
        return $this->getDecorated()->supports($entity);
    }
    
    public function setRegistry(SerializerRegistry $serializerRegistry): void
    {
        parent::setRegistry($serializerRegistry);
        $this->getDecorated()->setRegistry($serializerRegistry);
    }
}