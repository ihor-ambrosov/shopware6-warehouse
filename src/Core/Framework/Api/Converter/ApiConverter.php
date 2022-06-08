<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Framework\Api\Converter;

use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Symfony\Component\HttpFoundation\RequestStack;

class ApiConverter
{
    /**
     * @var DefinitionInstanceRegistry
     */
    private DefinitionInstanceRegistry $definitionInstanceRegistry;

    /**
     * @var RequestStack
     */
    private RequestStack $requestStack;

    public function __construct(
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        RequestStack $requestStack
    )
    {
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->requestStack = $requestStack;
    }

    public function convert(string $entityName, array $payload): array
    {
        if ($entityName !== 'product_warehouse') {
            return $payload;
        }
        if (!\array_key_exists('id', $payload)) {
            return $payload;
        }
        $definition = $this->definitionInstanceRegistry->getByEntityName($entityName);
        $primaryKeys = \explode('-', $payload['id']);
        foreach ($definition->getPrimaryKeys() as $primaryKeyField) {
            if ($primaryKeyField instanceof VersionField || $primaryKeyField instanceof ReferenceVersionField) {
                continue;
            }
            $primaryKeyPropertyName = $primaryKeyField->getPropertyName();
            if (\array_key_exists($primaryKeyPropertyName, $payload)) {
                continue;
            }
            $payload[$primaryKeyPropertyName] = \count($primaryKeys) ? \array_shift($primaryKeys) : null;
        }
        return $payload;
    }
}