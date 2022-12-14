<?php

namespace CoinGatePayment\Shopware6\Installer;

use CoinGatePayment\Shopware6\CoinGatePaymentShopware6;
use CoinGatePayment\Shopware6\PaymentHandler\CoinGatePaymentHandler;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\Plugin\Util\PluginIdProvider;

class PaymentMethodInstaller
{
    /**
     * @var PluginIdProvider
     */
    private $pluginIdProvider;

    /**
     * @var EntityRepositoryInterface
     */
    private $paymentMethodRepository;

    public function __construct(
        PluginIdProvider $pluginIdProvider,
        EntityRepositoryInterface $paymentMethodRepository
    ) {
        $this->pluginIdProvider = $pluginIdProvider;
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function install(InstallContext $context): void
    {
        $this->upsertPaymentMethod($context->getContext());
    }

    public function update(UpdateContext $context): void
    {
        $this->upsertPaymentMethod($context->getContext());
    }

    public function activate(ActivateContext $context): void
    {
        $this->setPaymentMethodIsActive(true, $context->getContext());
    }

    public function deactivate(DeactivateContext $context): void
    {
        $this->setPaymentMethodIsActive(false, $context->getContext());
    }

    public function uninstall(UninstallContext $context): void
    {
        $this->setPaymentMethodIsActive(false, $context->getContext());
    }

    private function upsertPaymentMethod(Context $context): void
    {
        $paymentMethodId = $this->getPaymentMethodId();

        $pluginId = $this->pluginIdProvider->getPluginIdByBaseClass(CoinGatePaymentShopware6::class, $context);

        $data = [
            'id' => $paymentMethodId,
            'handlerIdentifier' => CoinGatePaymentHandler::class,
            'pluginId' => $pluginId,
        ];

        $data = array_merge($data, [
            'name' => 'Pay with cryptocurrencies',
            'description' => 'Pay with bitcoin, ethereum, dogecoin or with over 70 other altcoins.',
        ]);

        if ($paymentMethodId) {
            $this->paymentMethodRepository->update([$data], $context);
        } else {
            $this->paymentMethodRepository->create([$data], $context);
        }
    }

    private function setPaymentMethodIsActive(bool $active, Context $context): void
    {
        $paymentMethodId = $this->getPaymentMethodId();
        // Payment does not even exist, so nothing to (de-)activate here
        if (! $paymentMethodId) {
            return;
        }

        $data = [
            'id' => $paymentMethodId,
            'active' => $active,
        ];

        $this->paymentMethodRepository->update([$data], $context);
    }

    private function getPaymentMethodId(): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('handlerIdentifier', CoinGatePaymentHandler::class));

        return $this->paymentMethodRepository->searchIds($criteria, Context::createDefaultContext())->firstId();
    }
}
