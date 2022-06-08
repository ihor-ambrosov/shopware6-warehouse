/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
import template from './eb-product-variants-overview.html.twig';
import './eb-products-variants-overview.scss';

const { Component } = Shopware;

Component.override('sw-product-variants-overview', {
    template,

    methods: {

        async getList() {
            return await this.$super('getList');
        },

        buildSearchQuery(criteria) {
            criteria.addAssociation('productWarehouses');
            criteria.addAssociation('productWarehouses.warehouse');
            return this.$super('buildSearchQuery', criteria);
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