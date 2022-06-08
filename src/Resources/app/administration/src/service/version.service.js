export function isVersionGreaterOrEqual(version) {
    return Shopware.Context.app.config.version.localeCompare(version, undefined, { numeric: true, sensitivity: 'base' }) >= 0;
}