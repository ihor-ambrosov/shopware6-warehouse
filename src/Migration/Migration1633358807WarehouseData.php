<?php declare(strict_types=1);
/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse\Migration;

use Doctrine\DBAL\Connection;
use Ambros\Warehouse\Defaults as WarehouseDefaults;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1633358807WarehouseData extends MigrationStep
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
        $hasData = $connection->executeQuery('SELECT 1 FROM `warehouse` LIMIT 1')->fetch();
        if ($hasData) {
            return;
        }
        $id = Uuid::fromHexToBytes(WarehouseDefaults::WAREHOUSE_ID);
        $code = WarehouseDefaults::WAREHOUSE_CODE;
        $languageEN = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $languageDE = Uuid::fromHexToBytes($this->getLanguageId($connection, 'de-DE'));
        $createdAt = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);
        $connection->insert(
            'warehouse',
            [
                'id' => $id,
                'code' => $code,
                'priority' => 0,
                'created_at' => $createdAt,
            ]
        );
        $connection->insert(
            'warehouse_translation',
            [
                'warehouse_id' => $id,
                'language_id' => $languageEN,
                'name' => 'Default',
                'created_at' => $createdAt
            ]
        );
        if ($languageDE) {
            $connection->insert(
                'warehouse_translation',
                [
                    'warehouse_id' => $id,
                    'language_id' => $languageDE,
                    'name' => 'Standard',
                    'created_at' => $createdAt
                ]
            );
        }
    }

    /**
     * @param Connection $connection
     * @return void
     */
    public function updateDestructive(Connection $connection): void
    {
    }

    /**
     * @param Connection $connection
     * @param string $languageCode
     * @return string|null
     */
    private function getLanguageId(Connection $connection, string $languageCode): ?string
    {
        $localeId = $this->getLocaleId($connection, $languageCode);
        if ($localeId === null) {
            return null;
        }
        $result = $connection->fetchColumn('SELECT LOWER(HEX(id)) FROM language WHERE locale_id = UNHEX(:localeId)', ['localeId' => $localeId]);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * @param Connection $connection
     * @param string $languageCode
     * @return string|null
     */
    private function getLocaleId(Connection $connection, string $languageCode): ?string
    {
        $result = $connection->fetchColumn('SELECT LOWER(HEX(id)) FROM locale WHERE code = :languageCode', ['languageCode' => $languageCode]);
        if ($result === false) {
            return null;
        }
        return (string) $result;
    }
}