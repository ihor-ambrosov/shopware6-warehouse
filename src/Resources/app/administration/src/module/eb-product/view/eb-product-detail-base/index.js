/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
import template from './sw-product-detail-base.html.twig';

const { Component } = Shopware;

Component.override('sw-product-detail-base', {
    template
});
