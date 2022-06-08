/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
import template from './eb-product-list.twig';
import './eb-product-list.scss';
import { isVersionGreaterOrEqual } from '../../../../service/version.service';

const { Component, Mixin } = Shopware;

const componentDataDefaultFilters = [
    'active-filter',
    'product-without-images-filter',
    'release-date-filter',
    'stock-filter',
    'price-filter',
    'manufacturer-filter',
    'visibilities-filter',
    'warehouses-filter',
    'categories-filter',
    'tags-filter'
];
const componentData = {
    products: null,
    currencies: [],
    sortBy: 'productNumber',
    sortDirection: 'DESC',
    naturalSorting: true,
    isLoading: false,
    isBulkLoading: false,
    total: 0,
    product: null,
    cloning: false,
    productEntityVariantModal: false,
    filterCriteria: [],
    defaultFilters: componentDataDefaultFilters,
    storeKey: 'grid.filter.product',
    activeFilterNumber: 0
};
if (isVersionGreaterOrEqual('6.4.2.0')) {
    componentData['showBulkEditModal'] = false;
}
if (isVersionGreaterOrEqual('6.4.5.0')) {
    componentData['searchConfigEntity'] = 'product';
}

Component.override('sw-product-list', {
    template,
    
    mixins: [
        Mixin.getByName('eb-version')
    ],
    
    data() {
        return componentData;
    },
    
    computed: {

        productCriteria() {
            const criteria = this.$super('productCriteria');
            criteria.addAssociation('productWarehouses');
            criteria.addAssociation('productWarehouses.warehouse');
            return criteria;
        },

        listFilters() {
            let filters = this.$super('listFilters');
            return [
                ...filters,
                ...this.filterFactory.create('product', {
                    'warehouses-filter': {
                        property: 'productWarehouses.warehouse',
                        label: this.$tc('eb-product.filters.warehousesFilter.label'),
                        placeholder: this.$tc('eb-product.filters.warehousesFilter.placeholder')
                    }
                })
            ];
        }
    },

    methods: {

        async getList() {
            return await this.$super('getList');
        },

        hasWarehouses(product) {
            return product.extensions.productWarehouses.length > 0;
        },

        getWarehouses(product) {
            return product.extensions.productWarehouses
                .map(productWarehouse => {
                    return productWarehouse.warehouse;
                })
                .sort((warehouse1, warehouse2) => {
                    return warehouse2.priority - warehouse1.priority;
                });
        },

        getProductWarehouse(product, warehouseId) {
            return product.extensions.productWarehouses.find(productWarehouse => productWarehouse.warehouseId === warehouseId);
        },

        getProductWarehouseField(product, warehouseId, field) {
            const productWarehouse = this.getProductWarehouse(product, warehouseId);
            if (productWarehouse) {
                return productWarehouse[field];
            }
            return null;
        },

        setProductWarehouseField(product, warehouseId, field, value) {
            const productWarehouse = this.getProductWarehouse(product, warehouseId);
            if (productWarehouse) {
                productWarehouse[field] = value;
            }
        }
    }
});