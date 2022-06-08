<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1634024102ProductWarehouse extends MigrationStep
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
        $connection->executeUpdate('
            CREATE TABLE `product_warehouse` (
              `product_id` BINARY(16) NOT NULL,
              `product_version_id` BINARY(16) NOT NULL,
              `warehouse_id` BINARY(16) NOT NULL,
              `stock` INT(11) NOT NULL DEFAULT 0,
              `available_stock` int(11) NOT NULL DEFAULT 0,
              `available` tinyint(1) NOT NULL DEFAULT 1,
              `sales` INT(11) NOT NULL DEFAULT 0,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`product_id`, `product_version_id`, `warehouse_id`),
              CONSTRAINT `fk.product_warehouse.product_id` FOREIGN KEY (`product_id`, `product_version_id`)
                REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.product_warehouse.warehouse_id` FOREIGN KEY (`warehouse_id`)
                REFERENCES `warehouse` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    /**
     * @param Connection $connection
     * @return void
     */
    public function updateDestructive(Connection $connection): void
    {
        
    }
}