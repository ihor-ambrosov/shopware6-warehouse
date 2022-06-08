/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
const { Component } = Shopware;

Component.override('sw-order-detail-base', {
    
    computed: {
        orderCriteria() {
            const criteria = this.$super('orderCriteria');
            criteria.addAssociation('warehouse');
            return criteria;
        }
    }
});