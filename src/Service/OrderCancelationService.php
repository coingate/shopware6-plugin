<?php

namespace CoinGatePayment\Shopware6\Service;

use CoinGatePayment\Shopware6\Shopware6\PaymentHandler\CoinGatePaymentHandler;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Exception;

class OrderCancelationService
{
    /**
     * @var ClientApiService
     */
    private $clientApiService;

    /**
     * @var OrderTransactionStateHandler
     */
    private $orderTransactionStateHandler;

    /**
     * @var EntityRepositoryInterface
     */
    private $stateMachineStateRepository;

    /**
     * @param ClientApiService $clientApiService
     * @param OrderTransactionStateHandler $orderTransactionStateHandler
     * @param EntityRepositoryInterface $stateMachineStateRepository
     */
    public function __construct(
        ClientApiService $clientApiService,
        OrderTransactionStateHandler $orderTransactionStateHandler,
        EntityRepositoryInterface $stateMachineStateRepository
    ) {
        $this->clientApiService = $clientApiService;
        $this->orderTransactionStateHandler = $orderTransactionStateHandler;
        $this->stateMachineStateRepository = $stateMachineStateRepository;
    }

    /**
     * @param OrderTransactionEntity $transaction
     * @param SalesChannelContext $salesChannelContext
     * @return bool
     *
     * @throws Exception
     */
    public function shouldBeCanceled(OrderTransactionEntity $transaction, SalesChannelContext $salesChannelContext): bool
    {
        /** @var int|null $coingateOrderId */
        $coingateOrderId = $transaction->getCustomFields()[CoinGatePaymentHandler::CUSTOM_FIELD_MAPPING_NAME];

        if (! $coingateOrderId) {
            return false;
        }

        try {
            $client = $this->clientApiService->get($salesChannelContext);

            $order = $client->order->get($coingateOrderId);

            //Newly created invoice. The shopper has not yet selected payment currency.
            if ($order->status === "new") {
                return true;
            }

            //Shopper selected payment currency. Awaiting payment.
            if ($order->status === "pending") {
                return true;
            }

            return false;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @param OrderTransactionEntity $transaction
     * @param Context $context
     */
    public function cancel(OrderTransactionEntity $transaction, Context $context)
    {
        $this->orderTransactionStateHandler->cancel($transaction->getId(), $context);
    }

    /**
     * @param Context $context
     * @return mixed|null
     */
    public function getCanceledStateMachineState(Context $context)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter("technicalName", "cancelled"));

        return $this->stateMachineStateRepository->search($criteria, $context)->first();
    }
}
