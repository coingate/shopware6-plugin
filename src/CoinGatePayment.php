<?php declare(strict_types=1);

namespace CoinGatePayment;

use CoinGatePayment\Installer\PaymentMethodInstaller;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\Plugin\Util\PluginIdProvider;

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

class CoinGatePayment extends Plugin
{
    public function install(InstallContext $context): void
    {
        $this->getPaymentMethodInstaller()->install($context);
    }

    public function update(UpdateContext $context): void
    {
        $this->getPaymentMethodInstaller()->update($context);
    }

    public function activate(ActivateContext $context): void
    {
        $this->getPaymentMethodInstaller()->activate($context);
    }

    public function deactivate(DeactivateContext $context): void
    {
        $this->getPaymentMethodInstaller()->deactivate($context);
    }

    public function uninstall(UninstallContext $context): void
    {
        $this->getPaymentMethodInstaller()->uninstall($context);

        if ($context->keepUserData()) {
            return;
        }

        // todo: any other custom removals if needed
    }

    private function getPaymentMethodInstaller(): PaymentMethodInstaller
    {
        $pluginIdProvider = $this->container->get(PluginIdProvider::class);

        $paymentMethodRepository = $this->container->get('payment_method.repository');

        return new PaymentMethodInstaller(
            $pluginIdProvider,
            $paymentMethodRepository
        );
    }
}
