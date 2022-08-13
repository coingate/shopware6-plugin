<?php

namespace CoinGatePayment\Shopware6\PaymentHandler;

use CoinGatePayment\Shopware6\Shopware6\Service\ClientApiService;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\AsynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\Exception\AsyncPaymentFinalizeException;
use Shopware\Core\Checkout\Payment\Exception\AsyncPaymentProcessException;
use Shopware\Core\Checkout\Payment\Exception\CustomerCanceledAsyncPaymentException;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Exception;

use Shopware\Core\Framework\Plugin\Util\PluginIdProvider;

class CoinGatePaymentHandler implements AsynchronousPaymentHandlerInterface
{
    public const CUSTOM_FIELD_MAPPING_NAME = 'coingate_order_id';

    /**
     * @var ClientApiService
     */
    private $clientApiService;

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var OrderTransactionStateHandler
     */
    private $orderTransactionStateHandler;

    /**
     * @var EntityRepositoryInterface
     */
    private $orderTransactionRepository;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        ClientApiService $clientApiService,
        SystemConfigService $systemConfigService,
        OrderTransactionStateHandler $orderTransactionStateHandler,
        EntityRepositoryInterface $orderTransactionRepository,
        RouterInterface $router
    ) {
        $this->clientApiService = $clientApiService;
        $this->systemConfigService = $systemConfigService;
        $this->orderTransactionStateHandler = $orderTransactionStateHandler;
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->router = $router;
    }

    /**
     * @param AsyncPaymentTransactionStruct $transaction
     * @param RequestDataBag $dataBag
     * @param SalesChannelContext $salesChannelContext
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function pay(AsyncPaymentTransactionStruct $transaction, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): RedirectResponse
    {
        try {
            $client = $this->clientApiService->get($salesChannelContext);

            /** @var CustomerEntity $customer */
            $customer = $transaction->getOrder()->getOrderCustomer();

            $order = $client->order->create([
                'order_id'          => $transaction->getOrder()->getOrderNumber(),
                'price_amount'      => $transaction->getOrderTransaction()->getAmount()->getTotalPrice(),
                'price_currency'    => $transaction->getOrder()->getCurrency()->getIsoCode(),
                'receive_currency'  => $this->systemConfigService->get('CoinGatePayment.config.receiveCurrency', $salesChannelContext->getSalesChannelId()) ?: 'DO_NOT_CONVERT',
                'callback_url'      => $this->router->generate('storefront.coingate.webhook', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url'        => $transaction->getReturnUrl() . '&cancel=1',
                'success_url'       => $transaction->getReturnUrl(),
                'title'             => 'Order #' . $transaction->getOrder()->getOrderNumber(),
                'description'       => sprintf("%s - %s %s (number: %s)", $salesChannelContext->getSalesChannel()->getName(), $customer->getFirstName(), $customer->getLastName(), $customer->getCustomerNumber()),
                'purchaser_email'   => $customer->getEmail()
            ]);

            // map ShopWare Order ID with CoinGate Order ID
            $this->orderTransactionRepository->update([
                [
                    'id' => $transaction->getOrderTransaction()->getId(),
                    'customFields' => [
                        self::CUSTOM_FIELD_MAPPING_NAME => $order->id,
                    ]
                ]
            ], $salesChannelContext->getContext());

        } catch (Exception $e) {
            throw new AsyncPaymentProcessException(
                $transaction->getOrderTransaction()->getId(),
                'An error occurred during the communication with an external payment gateway.' . PHP_EOL . $e->getMessage()
            );
        }

        return new RedirectResponse($order->payment_url);
    }

    /**
     * @param AsyncPaymentTransactionStruct $transaction
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     */
    public function finalize(AsyncPaymentTransactionStruct $transaction, Request $request, SalesChannelContext $salesChannelContext): void
    {
        $transactionId = $transaction->getOrderTransaction()->getId();

        if ($request->query->getBoolean('cancel')) {
            throw new CustomerCanceledAsyncPaymentException(
                $transactionId,
                'Customer canceled the payment on the CoinGate page.'
            );
        }

        /** @var array $customFields */
        $customFields = $transaction->getOrderTransaction()->getCustomFields();

        try {
            $client = $this->clientApiService->get($salesChannelContext);

            $order = $client->order->get($customFields[self::CUSTOM_FIELD_MAPPING_NAME] ?: null);

            switch ($order->status) {
                case 'canceled':
                case 'expired':
                case 'invalid':
                    throw new Exception('Invalid or expired payment.');
            }

        } catch (Exception $e) {
            throw new AsyncPaymentFinalizeException($transactionId, $e->getMessage());
        }
    }
}
