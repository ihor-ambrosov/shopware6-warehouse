/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
import template from './eb-product-warehouse-form.html.twig';

const { Component } = Shopware;
const { mapState } = Shopware.Component.getComponentHelper();

Component.register('eb-product-warehouse-form', {
    template,

    inject: ['feature'],

    props: {
        allowEdit: {
            type: Boolean,
            required: false,
            default: true
        }
    },

    computed: {
        ...mapState('swProductDetail', [
            'product'
        ])
    }
});