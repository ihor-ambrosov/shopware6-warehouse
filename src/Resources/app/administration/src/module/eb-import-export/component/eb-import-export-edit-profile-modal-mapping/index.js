/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
import template from './eb-import-export-edit-profile-modal-mapping.html.twig';

const { Component, Mixin, Data: { Criteria } } = Shopware;

Component.override('sw-import-export-edit-profile-modal-mapping', {
    template,
    
    mixins: [
        Mixin.getByName('eb-version')
    ],
    
    data() {
        return {
            warehouses: []
        };
    },
    
    computed: {
        
        warehouseRepository() {
            return this.repositoryFactory.create('warehouse');
        },
        
        warehouseCriteria() {
            const criteria = new Criteria();
            criteria.addSorting(Criteria.sort('priority', 'DESC'));
            return criteria;
        }
    },

    methods: {
        
        createdComponent() {
            if (this.isVersionGreaterOrEqual('6.4.1.0')) {
                this.warehouseRepository.search(this.warehouseCriteria).then(warehouses => {
                    this.warehouses = warehouses;
                });
            } else {
                this.warehouseRepository.search(this.warehouseCriteria, Shopware.Context.api).then(warehouses => {
                    this.warehouses = warehouses;
                });
            }
            this.$super('createdComponent');
        }
    }
});