<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Migration;

use Doctrine\DBAL\Connection;
use Ambros\Warehouse\Defaults as WarehouseDefaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1634024102ProductWarehouseData extends MigrationStep
{
    /**
     * @return int
     */
    public function getCreationTimestamp(): int
    {
        return 1634024102;
    }

    /**
     * @param Connection $connection
     * @return void
     */
    public function update(Connection $connection): void
    {
        $hasData = $connection->executeQuery('SELECT 1 FROM `product_warehouse` LIMIT 1')->fetch();
        if ($hasData) {
            return;
        }
        $warehouseId = Uuid::fromHexToBytes(WarehouseDefaults::WAREHOUSE_ID);
        $connection
            ->prepare('
                INSERT INTO `product_warehouse` (`product_id`, `product_version_id`, `warehouse_id`, `stock`, `available_stock`, `available`, `sales`, `created_at`)
                SELECT `id`, `version_id`, :warehouseId, `stock`, IFNULL(`available_stock`, 0), `available`, `sales`, NOW()
                FROM `product`;
            ')
            ->execute([ 'warehouseId' => $warehouseId ]);
    }

    /**
     * @param Connection $connection
     * @return void
     */
    public function updateDestructive(Connection $connection): void
    {
    }
}