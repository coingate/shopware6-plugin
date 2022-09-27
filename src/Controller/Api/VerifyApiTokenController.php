<?php

namespace CoinGatePayment\Shopware6\Controller\Api;

use CoinGatePayment\Shopware6\Service\ClientApiService;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Exception;

/**
 * @Route(defaults={"_routeScope"={"administration"}})
 */
class VerifyApiTokenController
{
    /**
     * @var ClientApiService
     */
    private $clientApiService;

    public function __construct(
        ClientApiService $clientApiService
    ) {
        $this->clientApiService = $clientApiService;
    }

    /**
     * @Route(path="/api/_action/coingate-payment/verify", methods={"POST"})
     *
     * @param RequestDataBag $dataBag
     * @return JsonResponse
     */
    public function check(RequestDataBag $dataBag): JsonResponse
    {
        $this->clientApiService->initUserAgent();

        $liveApiToken = $dataBag->get('CoinGatePaymentShopware6.config.apiToken', '');

        try {
            $liveStatus = \CoinGate\Client::testConnection($liveApiToken);
        } catch (Exception $e) {
            $liveStatus = false;
        }

        $sandboxApiToken = $dataBag->get('CoinGatePaymentShopware6.config.apiTokenForSandbox', '');

        try {
            $sandboxStatus = \CoinGate\Client::testConnection($sandboxApiToken, true);
        } catch (Exception $e) {
            $sandboxStatus = false;
        }

        return new JsonResponse([
            'live' => $liveStatus,
            'sandbox' => $sandboxStatus
        ]);
    }
}
