/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
import template from './eb-warehouse-list.html.twig';
import { isVersionGreaterOrEqual } from '../../../../service/version.service';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;
const componentInject = [
    'repositoryFactory',
    'acl',
    'warehouseApiService'
];
if (isVersionGreaterOrEqual('6.4.7.0') && !isVersionGreaterOrEqual('6.4.8.0')) {
    componentInject.push('feature');
}
const componentData = {
    defaultWarehouseId: null,
    warehouses: null,
    isLoading: true,
    sortBy: 'priority',
    sortDirection: 'DESC',
    total: 0
};
if (isVersionGreaterOrEqual('6.4.5.0')) {
    componentData['searchConfigEntity'] = 'warehouse';
}

Component.register('eb-warehouse-list', {
    template,
    
    inject: componentInject,
    
    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('listing'),
        Mixin.getByName('placeholder'),
        Mixin.getByName('eb-version')
    ],

    data() {
        return componentData;
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        warehouseRepository() {
            return this.repositoryFactory.create('warehouse');
        },

        warehouseColumns() {
            return [
                {
                    property: 'code',
                    dataIndex: 'code',
                    allowResize: true,
                    routerLink: 'eb.warehouse.detail',
                    label: 'eb-warehouse.list.columnCode',
                    inlineEdit: 'string',
                    primary: true
                },
                {
                    property: 'name',
                    dataIndex: 'name',
                    allowResize: true,
                    label: 'eb-warehouse.list.columnName',
                    inlineEdit: 'string'
                },
                {
                    property: 'priority',
                    dataIndex: 'priority',
                    allowResize: true,
                    label: 'eb-warehouse.list.columnPriority',
                    inlineEdit: 'number'
                }
            ];
        },

        warehouseCriteria() {
            const warehouseCriteria = new Criteria(this.page, this.limit);
            warehouseCriteria.setTerm(this.term);
            warehouseCriteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));
            return warehouseCriteria;
        },
        
        allowCreate() {
            return this.acl.can('warehouse.creator');
        },

        allowView() {
            return this.acl.can('warehouse.viewer');
        },

        allowEdit() {
            return this.acl.can('warehouse.editor');
        },

        allowInlineEdit() {
            return this.acl.can('warehouse.editor');
        },

        allowDelete() {
            return this.acl.can('warehouse.deleter');
        }
    },
    
    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.isLoading = true;
            this.warehouseApiService.getDefaultId()
                .then(data => {
                    this.defaultWarehouseId = data;
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        
        onChangeLanguage(languageId) {
            this.getList(languageId);
        },
        
        isDefault(warehouseId) {
            return warehouseId === this.defaultWarehouseId;
        },

        tooltipDelete(warehouseId) {
            if (!this.allowDelete && !this.isDefault(warehouseId)) {
                return {
                    message: this.$tc('sw-privileges.tooltip.warning'),
                    disabled: this.acl.can('warehouse.deleter'),
                    showOnDisabledElements: true
                };
            }
            return {
                message: '',
                disabled: true
            };
        },

        async getList() {
            this.isLoading = true;
            let searchResult = null;
            if (this.isVersionGreaterOrEqual('6.4.1.0')) {
                if (this.isVersionGreaterOrEqual('6.4.5.0')) {
                    const criteria = await this.addQueryScores(this.term, this.warehouseCriteria);
                    if (this.isVersionGreaterOrEqual('6.4.7.0') && !this.isVersionGreaterOrEqual('6.4.8.0')) {
                        if (this.feature.isActive('FEATURE_NEXT_6040') && !this.entitySearchable) {
                            this.isLoading = false;
                            this.total = 0;
                            return false;
                        }
                    } else if (this.isVersionGreaterOrEqual('6.4.8.0')) {
                        if (!this.entitySearchable) {
                            this.isLoading = false;
                            this.total = 0;
                            return false;
                        }
                    }
                    searchResult = this.warehouseRepository.search(criteria);
                } else {
                    searchResult = this.warehouseRepository.search(this.warehouseCriteria);
                }
            } else {
                searchResult = this.warehouseRepository.search(this.warehouseCriteria, Shopware.Context.api);
            }
            return searchResult.then(result => {
                this.warehouses = result;
                this.total = result.total;
                this.isLoading = false;
            });
        },
        
        onInlineEditSave(promise, warehouse) {
            const warehouseName = warehouse.name || this.placeholder(warehouse, 'name');
            return promise.then(() => {
                this.createNotificationSuccess({
                    message: this.$tc('eb-warehouse.list.messageSaveSuccess', 0, { name: warehouseName })
                });
            }).catch(() => {
                this.getList();
                this.createNotificationError({
                    message: this.$tc('eb-warehouse.list.messageSaveError')
                });
            });
        },

        updateTotal({ total }) {
            this.total = total;
        }
    }
});
