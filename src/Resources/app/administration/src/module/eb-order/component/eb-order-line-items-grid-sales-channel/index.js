/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
import template from './eb-order-line-items-grid-sales-channel.html.twig';

const { Component } = Shopware;

Component.override('sw-order-line-items-grid-sales-channel', {
    template,

    props: {
        warehouseId: {
            type: String,
            required: true,
            default: ''
        }
    }
});