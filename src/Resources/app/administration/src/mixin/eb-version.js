import { isVersionGreaterOrEqual } from '../service/version.service';

Shopware.Mixin.register('eb-version', {
    methods: {
        isVersionGreaterOrEqual(version) {
            return isVersionGreaterOrEqual(version);
        }
    }
});