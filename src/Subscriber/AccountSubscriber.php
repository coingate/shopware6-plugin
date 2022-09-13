<?php

namespace CoinGatePayment\Shopware6\Subscriber;

use CoinGatePayment\Shopware6\PaymentHandler\CoinGatePaymentHandler;
use CoinGatePayment\Shopware6\Service\OrderCancelationService;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Storefront\Page\Account\Order\AccountOrderPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AccountSubscriber implements EventSubscriberInterface
{
    /**
     * @var OrderCancelationService
     */
    private $orderCancelationService;

    public function __construct(
        OrderCancelationService $orderCancelationService
    ) {
        $this->orderCancelationService = $orderCancelationService;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AccountOrderPageLoadedEvent::class => 'onAccountOrderPageLoaded',
        ];
    }

    /**
     * When clicking on the "back" button in the browser after redirecting to the payment gateway, the user gets
     * redirected to the accountOrder page. Since we don't have any information about the transaction-state yet,
     * we simply take the last order and check whether it's a CoinGate order.
     * If the current state (state of the last transaction) is open, the chance the user pressed the "back" button is pretty high.
     * Therefore, we'll grab the CoinGate order ID from the customFields and check the actual status on CoinGate.
     * If the current status is indeed new or pending, we can cancel the order.
     * In case the user visits the accountOrder page after an actual payment and the state hasn't been updated until now,
     * the state would be set to canceled as well (as long as the actual state on CoinGate is new or pending).
     * But this shouldn't be a problem because the CoinGate webhook will set the proper state anyway.
     *
     * @param AccountOrderPageLoadedEvent $args
     */
    public function onAccountOrderPageLoaded(AccountOrderPageLoadedEvent $args)
    {
        /** @var OrderEntity $order */
        $order = $args->getPage()->getOrders()->first();

        if ($order->getStateMachineState()->getTechnicalName() == 'open') {
            /** @var OrderTransactionEntity $lastTransaction */
            $transaction = $order->getTransactions()->first();

            if ($this->orderCancelationService->shouldBeCanceled($transaction, $args->getSalesChannelContext())) {
                $this->orderCancelationService->cancel($transaction, $args->getContext());
            }
        }
    }
}
