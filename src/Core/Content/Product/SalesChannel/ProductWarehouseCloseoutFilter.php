<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Content\Product\SalesChannel;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;

class ProductWarehouseCloseoutFilter extends OrFilter
{
    public function __construct(string $warehouseId)
    {
        parent::__construct([
            new EqualsFilter('product.isCloseout', false),
            new AndFilter([
                new EqualsFilter('product.isCloseout', true),
                new EqualsFilter('productWarehouses.warehouseId', $warehouseId),
                new EqualsFilter('productWarehouses.available', true)
            ])
        ]);
    }
}