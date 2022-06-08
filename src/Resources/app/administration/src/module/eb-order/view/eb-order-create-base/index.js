/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
import template from './eb-order-create-base.html.twig';

const { Component } = Shopware;

Component.override('sw-order-create-base', {
    template,

    computed: {
        warehouseId() {
            if (!this.customer) {
                return null;
            }
            return this.customer.salesChannel.warehouseId;
        }
    }
});