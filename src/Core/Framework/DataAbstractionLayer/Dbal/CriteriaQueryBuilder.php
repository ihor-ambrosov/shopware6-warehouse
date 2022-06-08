<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Framework\DataAbstractionLayer\Dbal;

use Ambros\Warehouse\Core\Content\Product\SalesChannel\ProductWarehouseCloseoutFilter;
use Ambros\Warehouse\Core\Framework\ContextWarehouseExtension;
use Shopware\Core\Content\Product\SalesChannel\ProductCloseoutFilter;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\CriteriaQueryBuilder as ParentCriteriaQueryBuilder;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\QueryBuilder;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;

class CriteriaQueryBuilder extends ParentCriteriaQueryBuilder
{
    /**
     * @var ParentCriteriaQueryBuilder
     */
    private ParentCriteriaQueryBuilder $decorated;

    public function __construct(ParentCriteriaQueryBuilder $decorated) {
        $this->decorated = $decorated;
    }

    public function getDecorated(): ParentCriteriaQueryBuilder
    {
        return $this->decorated;
    }

    public function build(QueryBuilder $query, EntityDefinition $definition, Criteria $criteria, Context $context, array $paths = []): QueryBuilder
    {
        if ($this->isProductDefinition($definition) && $this->isProductCloseoutCriteriaFilterAdded($criteria)) {
            $this->replaceProductCloseoutCriteriaFilter($criteria, $context);
        }
        return $this->getDecorated()->build($query, $definition, $criteria, $context, $paths);
    }

    public function addFilter(EntityDefinition $definition, ?Filter $filter, QueryBuilder $query, Context $context): void
    {
        $this->getDecorated()->addFilter($definition, $filter, $query, $context);
    }

    public function addSortings(EntityDefinition $definition, Criteria $criteria, array $sortings, QueryBuilder $query, Context $context): void
    {
        $this->getDecorated()->addSortings($definition, $criteria, $sortings, $query, $context);
    }
    
    private function isProductDefinition(EntityDefinition $definition): bool
    {
        return $definition->getEntityName() === \Shopware\Core\Content\Product\ProductDefinition::ENTITY_NAME;
    }
    
    private function isProductCloseoutCriteriaFilterAdded(Criteria $criteria): bool
    {
        return \count(
            \array_filter($criteria->getFilters(), static function (Filter $filter) {
                return $filter instanceof ProductCloseoutFilter;
            })
        ) > 0;
    }

    private function replaceProductCloseoutCriteriaFilter(Criteria $criteria, Context $context): void
    {
        $warehouseId = $context->getExtension(ContextWarehouseExtension::KEY)->getWarehouseId();
        if (!$warehouseId) {
            return;
        }
        $filters = $criteria->getFilters();
        $criteria->resetFilters();
        foreach ($filters as $filter) {
            if (!($filter instanceof ProductCloseoutFilter)) {
                $criteria->addFilter($filter);
                continue;
            }
            $criteria->addFilter(new ProductWarehouseCloseoutFilter($warehouseId));
        }
    }
}