<?php declare(strict_types=1);

namespace Ambros\Warehouse\Core\System\SalesChannel\Context;

use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService as CoreSalesChannelContextService;

class SalesChannelContextService extends CoreSalesChannelContextService
{
    public const WAREHOUSE_ID = 'warehouseId';
}
