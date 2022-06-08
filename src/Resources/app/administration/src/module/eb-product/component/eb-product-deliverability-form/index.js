/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
import template from './sw-product-deliverability-form.html.twig';

const { Component } = Shopware;

Component.override('sw-product-deliverability-form', {
    template,

    props: {
        warehouseId: {
            type: String,
            required: false,
            default: null
        }
    },

    data() {
        return {
            currentWarehouseId: this.warehouseId
        };
    },
    
    computed: {
        productWarehouse() {
            return this.product.extensions.productWarehouses.find(productWarehouse => {
                return productWarehouse.warehouseId === this.currentWarehouseId;
            });
        },
        parentProductWarehouse() {
            if (!this.hasParentProduct()) {
                return null;
            }
            return this.parentProduct.extensions.productWarehouses.find(productWarehouse => {
                return productWarehouse.warehouseId === this.currentWarehouseId;
            });
        },
        warehouseIds() {
            return this.product.extensions.productWarehouses.map(entity => {
                return entity.warehouseId;
            });
        }
    },
    
    created() {
        this.createdComponent();
    },
    
    methods: {
        
        createdComponent() {
            if (typeof this.product.stock === 'undefined') {
                this.product.stock = 0;
            }
        },
        
        isWarehouseSelectVisible() {
            return !!this.warehouseIds.length;
        },
        
        isWarehouseSelected() {
            return !!this.currentWarehouseId;
        },
        
        hasParentProduct() {
            return !!this.parentProduct.id;
        },
        
        getFieldValue(field) {
            return this.productWarehouse ? this.productWarehouse[field] : this.product[field];
        },
        
        setFieldValue(field, value) {
            if (this.productWarehouse) {
                this.productWarehouse[field] = value;
            } else {
                this.product[field] = value;
            }
        },

        getInheritedFieldValue(field) {
            return this.parentProductWarehouse ? this.parentProductWarehouse[field] : this.parentProduct[field];
        },

        onWarehouseChanged(warehouseId) {
            this.currentWarehouseId = warehouseId;
        }
    }
});