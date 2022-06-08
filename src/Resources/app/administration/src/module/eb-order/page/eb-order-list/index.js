/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
const { Component, Mixin } = Shopware;
import { isVersionGreaterOrEqual } from '../../../../service/version.service';

const componentDataDefaultFilters = [
    'affiliate-code-filter',
    'campaign-code-filter',
    'document-filter',
    'warehouse-filter',
    'order-date-filter',
    'order-value-filter',
    'status-filter',
    'payment-status-filter',
    'delivery-status-filter',
    'payment-method-filter',
    'shipping-method-filter',
    'sales-channel-filter',
    'billing-country-filter',
    'customer-group-filter',
    'shipping-country-filter',
    'customer-group-filter',
    'tag-filter',
    'line-item-filter',
];
const componentData = {
    orders: [],
    sortBy: 'orderDateTime',
    sortDirection: 'DESC',
    isLoading: false,
    filterLoading: false,
    showDeleteModal: false,
    availableAffiliateCodes: [],
    affiliateCodeFilter: [],
    availableCampaignCodes: [],
    campaignCodeFilter: [],
    filterCriteria: [],
    defaultFilters: componentDataDefaultFilters,
    storeKey: 'grid.filter.order',
    activeFilterNumber: 0
};
if (isVersionGreaterOrEqual('6.4.2.0')) {
    componentData['showBulkEditModal'] = false;
    componentData['searchConfigEntity'] = 'order';
}

Component.override('sw-order-list', {
    
    mixins: [
        Mixin.getByName('eb-version')
    ],
    
    data() {
        return componentData;
    },
    
    computed: {
        
        orderCriteria() {
            const criteria = this.$super('orderCriteria');
            criteria.addAssociation('warehouse');
            return criteria;
        },
        
        listFilters() {
            let filters = this.$super('listFilters');
            return [
                ...filters,
                ...this.filterFactory.create('order', {
                    'warehouse-filter': {
                        property: 'warehouse',
                        label: this.$tc('eb-order.filters.warehouseFilter.label'),
                        placeholder: this.$tc('eb-order.filters.warehouseFilter.placeholder')
                    }
                })
            ];
        }
    },

    methods: {
        async getList() {
            return await this.$super('getList');
        },
        
        async onBulkEditItems() {
            if (this.isVersionGreaterOrEqual('6.4.4.0')) {
                await this.$super('onBulkEditItems');
            }
        },
        
        getOrderColumns() {
            const columns = this.$super('getOrderColumns');
            columns.push({
                property: 'extensions.warehouse.translated.name',
                label: 'eb-order.list.columnWarehouse',
                allowResize: true
            });
            return columns;
        }
    }
});