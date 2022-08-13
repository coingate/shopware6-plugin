<?php

namespace CoinGatePayment\Shopware6\Service;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClientApiService
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(
        SystemConfigService $systemConfigService
    ) {
        $this->systemConfigService = $systemConfigService;

        \CoinGate\Client::setAppInfo('ShopWare6 Extension', '1.0.0');
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @return \CoinGate\Client
     */
    public function get(SalesChannelContext $salesChannelContext): \CoinGate\Client
    {
        $salesChannelId = $salesChannelContext->getSalesChannelId();

        $isSandboxEnv = $this->systemConfigService->get('CoinGatePayment.config.isLiveMode', $salesChannelId) !== true;

        $apiToken = $isSandboxEnv
            ? $this->systemConfigService->get('CoinGatePayment.config.apiTokenForSandbox', $salesChannelId)
            : $this->systemConfigService->get('CoinGatePayment.config.apiToken', $salesChannelId);

        return new \CoinGate\Client($apiToken, $isSandboxEnv);
    }
}
