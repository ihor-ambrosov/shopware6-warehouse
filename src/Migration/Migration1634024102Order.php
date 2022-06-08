<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1634024102Order extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1634024102;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('ALTER TABLE `order` ADD COLUMN `warehouse_id` BINARY(16) NULL AFTER `currency_id`');
        $connection->executeUpdate('
            ALTER TABLE `order` ADD CONSTRAINT `fk.order.warehouse_id`
            FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}