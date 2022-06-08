/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
import template from './eb-order-create-details-footer.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-order-create-details-footer', {
    template,
    
    inject: ['warehouseApiService'],
    
    data() {
        return {
            defaultWarehouseId: null
        };
    },

    computed: {
        warehouseCriteria() {
            const criteria = new Criteria();
            criteria.addSorting(Criteria.sort('priority', 'DESC'));
            return criteria;
        }
    },
    
    created() {
        this.createdComponent();
    },

    methods: {
        
        createdComponent() {
            this.warehouseApiService.getDefaultId()
                .then(data => {
                    this.defaultWarehouseId = data;
                })
                .finally(() => {
                });
        },
        
        updateContext() {
            this.$super('updateContext');
            const contextKeys = ['warehouseId'];
            contextKeys.forEach((key) => {
                this.context[key] = this.context[key] || this.defaultWarehouseId;
            });
        }
    }
});