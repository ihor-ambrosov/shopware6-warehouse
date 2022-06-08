/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
const { Component } = Shopware;

Component.override('sw-product-detail', {
    
    computed: {
        productCriteria() {
            const criteria = this.$super('productCriteria');
            criteria.addAssociation('productWarehouses');
            criteria.addAssociation('productWarehouses.warehouse');
            return criteria;
        },
        
        getModeSettingGeneralTab() {
            const tab = this.$super('getModeSettingGeneralTab');
            tab.push({
                key: 'warehouse',
                label: 'eb-product.detailBase.cardTitleWarehouse',
                enabled: true,
                name: 'general'
            });
            return tab;
        }
    },

    methods: {
        createdComponent() {
            this.$super('createdComponent');
            const modeSettings = this.modeSettings;
            modeSettings.push('warehouse');
            Shopware.State.commit('swProductDetail/setModeSettings', modeSettings);
        }
    }
});