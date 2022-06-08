/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-order-product-select', {

    props: {
        warehouseId: {
            type: String,
            required: true,
            default: ''
        }
    },

    computed: {
        productCriteria() {
            const criteria = this.$super('productCriteria');
            if (this.warehouseId) {
                criteria.addFilter(Criteria.equals('productWarehouses.warehouseId', this.warehouseId));
            }
            return criteria;
        }
    }
});