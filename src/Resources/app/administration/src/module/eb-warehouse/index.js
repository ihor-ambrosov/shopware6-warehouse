/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
import './component/eb-shortcut-overview';
import './page/eb-warehouse-list';
import './page/eb-warehouse-detail';
import './acl';
import WarehouseApiService from './service/api/warehouse.api.service';

const { Application, Module, Service, Shortcut } = Shopware;

Module.register('eb-warehouse', {
    type: 'plugin',
    name: 'warehouse',
    title: 'eb-warehouse.general.mainMenuItemGeneral',
    description: 'Manage warehouses',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#57D9A3',
    icon: 'default-symbol-products',
    favicon: 'icon-module-products.png',
    entity: 'warehouse',
    routes: {
        index: {
            components: {
                default: 'eb-warehouse-list'
            },
            path: 'index',
            meta: {
                privilege: 'warehouse.viewer'
            }
        },
        create: {
            component: 'eb-warehouse-detail',
            path: 'create',
            meta: {
                parentPath: 'eb.warehouse.index',
                privilege: 'warehouse.creator'
            }
        },
        detail: {
            component: 'eb-warehouse-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'eb.warehouse.index',
                privilege: 'warehouse.viewer'
            },
            props: {
                default(route) {
                    return {
                        warehouseId: route.params.id
                    };
                }
            }
        }
    },
    navigation: [{
        id: 'eb-warehouse',
        label: 'eb-warehouse.general.mainMenuItemList',
        color: '#57D9A3',
        path: 'eb.warehouse.index',
        icon: 'default-symbol-products',
        privilege: 'warehouse.viewer',
        parent: 'sw-catalogue',
        position: 60
    }]
});
Shortcut.register('AW', '/eb/warehouse/create');
Shortcut.register('GW', '/eb/warehouse/index');
Service().register('warehouseApiService', (container) => {
    return new WarehouseApiService(
        Application.getContainer('init').httpClient,
        container.loginService
    );
});