/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
import template from './eb-product-warehouse-select.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;
const { EntityCollection } = Shopware.Data;
const { mapState } = Shopware.Component.getComponentHelper();

Component.extend('eb-product-warehouse-select', 'sw-entity-multi-select', {
    template,

    data() {
        return {
            
        };
    },

    computed: {
        ...mapState('swProductDetail', [
            'product'
        ]),
        criteria() {
            const criteria = new Criteria();
            criteria.addSorting(Criteria.sort('priority', 'DESC'));
            return criteria;
        },
        repository() {
            return this.repositoryFactory.create('warehouse');
        },
        associationRepository() {
            return this.repositoryFactory.create('product_warehouse');
        }
    },

    methods: {
        isSelected(item) {
            return this.currentCollection.some(entity => {
                return entity.warehouseId === item.id;
            });
        },

        addItem(item) {
            if (this.isSelected(item)) {
                const associationEntity = this.currentCollection.find(entity => {
                    return entity.warehouseId === item.id;
                });
                this.remove(associationEntity);
                return;
            }
            const newAssociationEntity = this.associationRepository.create(this.entityCollection.context);
            newAssociationEntity.productId = this.product.id;
            newAssociationEntity.productVersionId = this.product.versionId;
            newAssociationEntity.warehouseId = item.id;
            newAssociationEntity.stock = 0;
            newAssociationEntity.warehouse = item;
            this.$emit('item-add', item);
            const changedCollection = EntityCollection.fromCollection(this.currentCollection);
            changedCollection.add(newAssociationEntity);
            this.emitChanges(changedCollection);
            this.onSelectExpanded();
        },
        
        emitChanges(newCollection) {
            this.$super('emitChanges', newCollection);
        }
    }
});
