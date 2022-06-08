<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1633358807Warehouse extends MigrationStep
{
    /**
     * @return int
     */
    public function getCreationTimestamp(): int
    {
        return 1633358807;
    }

    /**
     * @param Connection $connection
     * @return void
     */
    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
            CREATE TABLE `warehouse` (
                `id` BINARY(16) NOT NULL,
                `code` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `priority` int(11) NOT NULL DEFAULT \'0\',
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
        $connection->executeUpdate('
            CREATE TABLE `warehouse_translation` (
                `warehouse_id` BINARY(16) NOT NULL,
                `language_id` BINARY(16) NOT NULL,
                `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`warehouse_id`, `language_id`),
                CONSTRAINT `fk.warehouse_translation.language_id` FOREIGN KEY (`language_id`)
                    REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.warehouse_translation.warehouse_id` FOREIGN KEY (`warehouse_id`)
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
