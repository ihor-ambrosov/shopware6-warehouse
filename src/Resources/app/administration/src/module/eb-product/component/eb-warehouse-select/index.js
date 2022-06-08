/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
import template from './eb-warehouse-select.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('eb-warehouse-select', {
    template,

    props: {
        warehouseIds: {
            type: Array,
            required: false,
            default() {
                return [];
            }
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false
        },
        label: {
            type: String,
            required: false,
            default: ''
        }
    },

    data() {
        return {
            warehouseId: null
        };
    },
    
    computed: {
        warehouseCriteria() {
            const criteria = new Criteria();
            criteria.setIds(this.warehouseIds);
            criteria.addSorting(Criteria.sort('priority', 'DESC'));
            return criteria;
        }
    },

    watch: {
        warehouseIds(value) {
            if (!value.includes(this.warehouseId)) {
                this.onChange(null);
            }
        }
    },
    
    methods: {
        onChange(id) {
            this.warehouseId = id;
            this.emitChange();
        },
        emitChange() {
            this.$emit('change', this.warehouseId);
        }
    }
});
