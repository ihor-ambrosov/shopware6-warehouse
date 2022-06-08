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

class Migration1634024102OrderData extends MigrationStep
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
        $warehouseId = Uuid::fromHexToBytes(WarehouseDefaults::WAREHOUSE_ID);
        $connection->executeUpdate(
            'UPDATE `order` SET `warehouse_id` = :warehouseId WHERE `warehouse_id` IS NULL',
            [ 'warehouseId' => $warehouseId ]
        );
    }

    /**
     * @param Connection $connection
     * @return void
     */
    public function updateDestructive(Connection $connection): void
    {
    }
}