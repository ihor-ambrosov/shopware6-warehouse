/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
const { Component } = Shopware;

Component.override('sw-import-export-entity-path-select', {

    props: {
        warehouses: {
            type: Array,
            required: false,
            default() {
                return [];
            }
        }
    },

    data() {
        return {
            productWarehousesProperties: [ 'stock', 'availableStock' ]
        };
    },

    computed: {

        actualPathParts() {
            let pathParts = this.$super('actualPathParts');
            return pathParts.filter(part => {
                if (this.availableWarehouseCodes.includes(part)) {
                    return false;
                }
                return part !== 'productWarehouses';
            });
        },

        processFunctions() {
            let functions = this.$super('processFunctions');
            functions.unshift(this.processProductWarehouses);
            return functions;
        },

        availableWarehouseCodes() {
            return this.warehouses.map(warehouse => warehouse.code);
        }
    },

    methods: {
        
        processProductWarehouses({ definition, options, properties, path }) {
            const productWarehousesProperty = definition.properties.productWarehouses;
            if (!productWarehousesProperty || productWarehousesProperty.relation !== 'one_to_many') {
                return { properties, options, definition, path };
            }
            const newOptions = [...options, ...this.getProductWarehousesOptions(path)];
            return {
                properties: properties,
                options: newOptions,
                definition: definition,
                path: path
            };
        },
        
        getProductWarehousesOptions(path) {
            const options = [];
            this.availableWarehouseCodes.forEach((code) => {
                this.productWarehousesProperties.forEach(propertyName => {
                    const name = `${path}productWarehouses.${code}.${propertyName}`;
                    options.push({ label: name, value: name });
                });
            });
            return options;
        }
    }
});