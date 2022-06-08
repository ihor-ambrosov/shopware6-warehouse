<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Storefront\Pagelet\Header;

use Ambros\Warehouse\Core\Content\Warehouse\WarehouseCollection;
use Ambros\Warehouse\Core\Content\Warehouse\WarehouseEntity;
use Shopware\Core\Framework\Struct\Struct;

class HeaderPageletWarehouseExtension extends Struct
{
    public const KEY = 'warehouse';

    /**
     * @var WarehouseCollection
     */
    protected WarehouseCollection $warehouses;

    /**
     * @var WarehouseEntity
     */
    protected WarehouseEntity $activeWarehouse;

    public function __construct(
        WarehouseCollection $warehouses,
        WarehouseEntity $activeWarehouse
    )
    {
        $this->warehouses = $warehouses;
        $this->activeWarehouse = $activeWarehouse;
    }

    public function getWarehouses(): WarehouseCollection
    {
        return $this->warehouses;
    }

    public function getActiveWarehouse(): WarehouseEntity
    {
        return $this->activeWarehouse;
    }

    public function getApiAlias(): string
    {
        return 'header_pagelet_warehouse_extension';
    }
}
