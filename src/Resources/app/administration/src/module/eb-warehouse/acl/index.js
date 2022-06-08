/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
Shopware.Service('privileges')
    .addPrivilegeMappingEntry({
        category: 'permissions',
        parent: 'catalogues',
        key: 'warehouse',
        roles: {
            viewer: {
                privileges: [
                    'warehouse:read'
                ],
                dependencies: []
            },
            editor: {
                privileges: [
                    'warehouse:update'
                ],
                dependencies: [
                    'warehouse.viewer'
                ]
            },
            creator: {
                privileges: [
                    'warehouse:create'
                ],
                dependencies: [
                    'warehouse.viewer',
                    'warehouse.editor'
                ]
            },
            deleter: {
                privileges: [
                    'warehouse:delete'
                ],
                dependencies: [
                    'warehouse.viewer'
                ]
            }
        }
    });
