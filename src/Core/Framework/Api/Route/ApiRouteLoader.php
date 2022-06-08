<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Framework\Api\Route;

use Shopware\Core\Framework\Api\Route\ApiRouteLoader as ParentApiRouteLoader;
use Shopware\Core\Framework\Api\Controller\ApiController;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Extension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\AssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;

class ApiRouteLoader extends ParentApiRouteLoader
{
    /**
     * @var ParentApiRouteLoader
     */
    private ParentApiRouteLoader $decorated;

    private DefinitionInstanceRegistry $definitionRegistry;

    private bool $isLoaded = false;

    public function __construct(
        ParentApiRouteLoader $decorated,
        DefinitionInstanceRegistry $definitionRegistry
    )
    {
        $this->decorated = $decorated;
        $this->definitionRegistry = $definitionRegistry;
    }

    public function getDecorated(): ParentApiRouteLoader
    {
        return $this->decorated;
    }
    
    public function load($resource, ?string $type = null): RouteCollection
    {
        if ($this->isLoaded) {
            throw new \RuntimeException('Do not add the "api" loader twice');
        }
        $routes = $this->getDecorated()->load($resource, $type);
        $propertyNames = [
            'product' => ['productWarehouses']
        ];
        $defaultPropertyPath = '(\/(extensions\/)?[a-zA-Z-]+\/[0-9a-f]{32})*';
        foreach ($propertyNames as $entityName => $entityPropertyNames) {
            $definition = $this->definitionRegistry->getByEntityName($entityName);
            $propertiesPaths = [];
            foreach ($entityPropertyNames as $propertyName) {
                $field = $definition->getField($propertyName);
                if (empty($field) || !($field instanceof AssociationField)) {
                    continue;
                }
                $isExtension = $field->is(Extension::class);
                $referencePrimaryKeysCount = \count(\array_filter(\iterator_to_array($field->getReferenceDefinition()->getPrimaryKeys()), function ($primaryKey) {
                    return !($primaryKey instanceof VersionField) && !($primaryKey instanceof ReferenceVersionField);
                }));
                $propertiesPaths[] = '\/'.($isExtension ? 'extensions\/' : '').$propertyName.'\/[0-9a-f]{32}([-][0-9a-f]{32}){'.($referencePrimaryKeysCount - 1).'}';
            }
            $path = '[0-9a-f]{32}('.\implode('|', $propertiesPaths).')?'.$defaultPropertyPath.'\/?$';
            $this->addRoute($routes, $entityName, 'detail', 'GET', $path);
            $this->addRoute($routes, $entityName, 'update', 'PATCH', $path);
            $this->addRoute($routes, $entityName, 'delete', 'DELETE', $path);
        }
        $this->isLoaded = true;
        return $routes;
    }

    public function supports($resource, ?string $type = null): bool
    {
        return $this->getDecorated()->supports($resource, $type);
    }
    
    public function getResolver()
    {
        return $this->getDecorated()->getResolver();
    }

    public function setResolver(LoaderResolverInterface $resolver)
    {
        $this->getDecorated()->setResolver($resolver);
    }

    public function import($resource, string $type = null)
    {
        return $this->getDecorated()->import($resource, $type);
    }

    public function resolve($resource, string $type = null)
    {
        return $this->getDecorated()->resolve($resource, $type);
    }
    
    private function addRoute(RouteCollection $routes, string $entityName, string $methodName, string $httpMethod, string $path): void
    {
        $resourceName = \str_replace('_', '-', $entityName);
        $route = new Route('/api/'.$resourceName.'/{path}');
        $route->setMethods([$httpMethod]);
        $route->setDefault('_controller', ApiController::class.'::'.$methodName);
        $route->setDefault('entityName', $resourceName);
        $route->addRequirements(['path' => $path, 'version' => '\d+']);
        $routes->add('api.'.$entityName.'.'.$methodName, $route);
    }
}
