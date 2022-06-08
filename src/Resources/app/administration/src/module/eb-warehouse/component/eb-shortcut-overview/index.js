/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
const { Component } = Shopware;
const utils = Shopware.Utils;

Component.override('sw-shortcut-overview', {
    
    computed: {
        sections() {
            let sections = this.$super('sections');
            sections.addingItems.push({
                id: utils.createId(),
                title: this.$tc('eb-shortcut-overview.functionAddWarehouse'),
                content: this.$tc('eb-shortcut-overview.keyboardShortcutAddWarehouse')
            });
            sections.navigation.push({
                id: utils.createId(),
                title: this.$tc('eb-shortcut-overview.functionGoToWarehouses'),
                content: this.$tc('eb-shortcut-overview.keyboardShortcutGoToWarehouses')
            });
            return sections;
        }
    }
});