<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Framework\DataAbstractionLayer\Cache;

use Ambros\Warehouse\Core\System\SalesChannel\SalesChannelContextWarehouseExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator as ParentEntityCacheKeyGenerator;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class EntityCacheKeyGenerator extends ParentEntityCacheKeyGenerator
{
    /**
     * @var ParentEntityCacheKeyGenerator
     */
    private ParentEntityCacheKeyGenerator $decorated;

    public function __construct(ParentEntityCacheKeyGenerator $decorated) {
        $this->decorated = $decorated;
    }

    public function getDecorated(): ParentEntityCacheKeyGenerator
    {
        return $this->decorated;
    }
    
    public static function buildCmsTag(string $id): string
    {
        return ParentEntityCacheKeyGenerator::buildCmsTag($id);
    }

    public static function buildProductTag(string $id): string
    {
        return ParentEntityCacheKeyGenerator::buildProductTag($id);
    }

    public static function buildStreamTag(string $id): string
    {
        return ParentEntityCacheKeyGenerator::buildStreamTag($id);
    }

    public function getSalesChannelContextHash(SalesChannelContext $context): string
    {
        return md5(json_encode([
            $context->getSalesChannelId(),
            $context->getDomainId(),
            $context->getLanguageIdChain(),
            $context->getVersionId(),
            $context->getCurrencyId(),
            $context->getRuleIds(),
            $context->getExtension(SalesChannelContextWarehouseExtension::KEY)->getWarehouse()->getId(),
        ]));
    }

    public function getCriteriaHash(Criteria $criteria): string
    {
        return $this->getDecorated()->getCriteriaHash($criteria);
    }
}