<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\System\SalesChannel;

use Ambros\Warehouse\Core\Content\Warehouse\WarehouseEntity;
use Shopware\Core\Framework\Struct\Struct;

class SalesChannelContextWarehouseExtension extends Struct
{
    public const KEY = 'warehouse';

    /**
     * @var WarehouseEntity
     */
    protected WarehouseEntity $warehouse;

    public function __construct(WarehouseEntity $warehouse) {
        $this->warehouse = $warehouse;
    }

    public function getWarehouse(): WarehouseEntity
    {
        return $this->warehouse;
    }
}