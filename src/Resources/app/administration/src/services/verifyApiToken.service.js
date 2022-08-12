const ApiService = Shopware.Classes.ApiService;
const { Application } = Shopware;

class VerifyApiTokenService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'coingate-payment') {
        super(httpClient, loginService, apiEndpoint);
    }

    check(data) {
        const apiRoute = `_action/${this.getApiBasePath()}/verify`

        return this.httpClient
            .post(apiRoute, data, { headers: this.getBasicHeaders() })
            .then(response => ApiService.handleResponse(response));
    }
}

Application.addServiceProvider('verifyApiTokenService', (container) => {
    const initContainer = Application.getContainer('init');

    return new VerifyApiTokenService(initContainer.httpClient, container.loginService);
})
