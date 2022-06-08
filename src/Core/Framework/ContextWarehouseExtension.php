<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Core\Framework;

use Shopware\Core\Framework\Struct\Struct;

class ContextWarehouseExtension extends Struct
{
    public const KEY = 'warehouse';

    /**
     * @var string
     */
    protected $warehouseId;

    public function __construct(string $warehouseId) {
        $this->warehouseId = $warehouseId;
    }

    public function getWarehouseId(): string
    {
        return $this->warehouseId;
    }
}