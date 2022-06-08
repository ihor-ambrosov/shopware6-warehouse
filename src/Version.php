<?php declare(strict_types=1);
/**
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
namespace Ambros\Warehouse;

use Composer\InstalledVersions;

final class Version
{
    /**
     * @var string
     */
    private static $version;
    
    /**
     * @return string
     */
    public static function getVersion(): string
    {
        if (self::$version !== null) {
            return self::$version;
        }
        $version = InstalledVersions::getVersion('shopware/core');
        if (!$version) {
            $version = InstalledVersions::getVersion('shopware/platform');
        }
        return self::$version = $version;
    }

    /**
     * @param string $version
     * @return bool
     */
    public static function isVersionGreaterOrEqual(string $version): bool
    {
        return (bool) \version_compare(self::getVersion(), $version, '>=');
    }
}