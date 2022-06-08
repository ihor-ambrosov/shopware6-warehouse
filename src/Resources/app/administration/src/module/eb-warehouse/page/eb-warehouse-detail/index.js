/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
import template from './eb-warehouse-detail.html.twig';
import './eb-warehouse-detail.scss';

const { Component, Mixin, Data: { Criteria } } = Shopware;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

Component.register('eb-warehouse-detail', {
    template,

    inject: [
        'repositoryFactory',
        'acl',
        'warehouseApiService'
    ],

    mixins: [
        Mixin.getByName('placeholder'),
        Mixin.getByName('notification'),
        Mixin.getByName('discard-detail-page-changes')('warehouse'),
        Mixin.getByName('eb-version')
    ],

    shortcuts: {
        'SYSTEMKEY+S': 'onSave',
        ESCAPE: 'onCancel'
    },

    props: {
        warehouseId: {
            type: String,
            required: false,
            default: null
        }
    },


    data() {
        return {
            defaultWarehouseId: null,
            warehouse: null,
            isLoading: false,
            isSaveSuccessful: false
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier)
        };
    },

    computed: {
        identifier() {
            return this.placeholder(this.warehouse, 'name');
        },

        warehouseIsLoading() {
            return this.isLoading || this.warehouse == null;
        },

        warehouseRepository() {
            return this.repositoryFactory.create('warehouse');
        },

        tooltipSave() {
            if (this.acl.can('warehouse.editor')) {
                const systemKey = this.$device.getSystemKey();
                return {
                    message: `${systemKey} + S`,
                    appearance: 'light'
                };
            }
            return {
                showDelay: 300,
                message: this.$tc('sw-privileges.tooltip.warning'),
                disabled: this.acl.can('order.editor'),
                showOnDisabledElements: true
            };
        },

        tooltipCancel() {
            return {
                message: 'ESC',
                appearance: 'light'
            };
        },

        ...mapPropertyErrors('warehouse', ['code', 'name', 'priority'])
    },

    watch: {
        warehouseId() {
            this.createdComponent();
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
                    if (this.warehouseId) {
                        this.loadEntityData();
                        return;
                    }
                    Shopware.State.commit('context/resetLanguageToDefault');
                    if (this.isVersionGreaterOrEqual('6.4.1.0')) {
                        this.warehouse = this.warehouseRepository.create();
                    } else {
                        this.warehouse = this.warehouseRepository.create(Shopware.Context.api);
                    }
                });
        },

        loadEntityData() {
            this.isLoading = true;
            let getResult = null;
            if (this.isVersionGreaterOrEqual('6.4.1.0')) {
                getResult = this.warehouseRepository.get(this.warehouseId);
            } else {
                getResult = this.warehouseRepository.get(this.warehouseId, Shopware.Context.api);
            }
            getResult.then((warehouse) => {
                this.isLoading = false;
                this.warehouse = warehouse;
            });
        },

        abortOnLanguageChange() {
            return this.warehouseRepository.hasChanges(this.warehouse);
        },

        saveOnLanguageChange() {
            return this.onSave();
        },

        onChangeLanguage() {
            this.loadEntityData();
        },
        
        isDefault(warehouseId) {
            return warehouseId === this.defaultWarehouseId;
        },

        onSave() {
            if (!this.acl.can('warehouse.editor')) {
                return;
            }
            this.isLoading = true;
            let saveResult = null;
            if (this.isVersionGreaterOrEqual('6.4.1.0')) {
                saveResult = this.warehouseRepository.save(this.warehouse);
            } else {
                saveResult = this.warehouseRepository.save(this.warehouse, Shopware.Context.api);
            }
            saveResult
                .then(() => {
                    this.isLoading = false;
                    this.isSaveSuccessful = true;
                    if (this.warehouseId === null) {
                        this.$router.push({ name: 'eb.warehouse.detail', params: { id: this.warehouse.id } });
                        return;
                    }
                    this.createNotificationSuccess({
                        message: this.$tc('eb-warehouse.detail.messageSaveSuccess', 0, { name: this.warehouse.name })
                    });
                    this.loadEntityData();
                })
                .catch((exception) => {
                    this.isLoading = false;
                    this.createNotificationError({ message: this.$tc('eb-warehouse.detail.messageSaveError') });
                    throw exception;
                });
        },

        onCancel() {
            this.$router.push({ name: 'eb.warehouse.index' });
        }
    }
});
