<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Warehouse\SalesChannel;

use Ambros\Warehouse\Core\Content\Warehouse\SalesChannel\WarehouseRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractWarehouseRoute
{
    abstract public function getDecorated(): AbstractWarehouseRoute;

    abstract public function load(Request $request, SalesChannelContext $context, Criteria $criteria): WarehouseRouteResponse;
}
