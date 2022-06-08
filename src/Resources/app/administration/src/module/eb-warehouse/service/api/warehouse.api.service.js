/*
 * @author Ihor Ambrosov <ihor.ambrosov@gmail.com>
 * @license https://opensource.org/licenses/OSL-3.0
 */
const ApiService = Shopware.Classes.ApiService;

class WarehouseApiService extends ApiService {

    constructor(httpClient, loginService, apiEndpoint = 'warehouse') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'warehouseApiService';
    }

    getDefaultId(additionalParams = {}, additionalHeaders = {}) {
        return this.httpClient
            .get('_action/warehouse/get-default-id', {
                params: { ...additionalParams },
                headers: this.getBasicHeaders(additionalHeaders)
            })
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}

export default WarehouseApiService;