<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Warehouse\SalesChannel;

use Ambros\Warehouse\Core\Content\Warehouse\WarehouseCollection;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class WarehouseRouteResponse extends StoreApiResponse
{
    /**
     * @var WarehouseCollection
     */
    protected $object;

    public function __construct(WarehouseCollection $warehouses)
    {
        parent::__construct($warehouses);
    }

    public function getWarehouses(): WarehouseCollection
    {
        return $this->object;
    }
}
