<?php

namespace CoinGatePayment\Shopware6\Controller;

use CoinGatePayment\Shopware6\Shopware6\PaymentHandler\CoinGatePaymentHandler;
use CoinGatePayment\Shopware6\Shopware6\Service\ClientApiService;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Exception;

/**
 * @Route(defaults={"_routeScope"={"storefront"}, "csrf_protected"=false})
 */
class WebhookController extends StorefrontController
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
    private $orderTransactionRepository;

    public function __construct(
        ClientApiService $clientApiService,
        OrderTransactionStateHandler $orderTransactionStateHandler,
        EntityRepositoryInterface $orderTransactionRepository
    ) {
        $this->clientApiService = $clientApiService;
        $this->orderTransactionStateHandler = $orderTransactionStateHandler;
        $this->orderTransactionRepository = $orderTransactionRepository;
    }

    /**
     * @param $transactionId
     * @param SalesChannelContext $salesChannelContext
     * @return mixed
     *
     * @throws Exception
     */
    private function getOrderTransaction($transactionId, SalesChannelContext $salesChannelContext)
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customFields.' . CoinGatePaymentHandler::CUSTOM_FIELD_MAPPING_NAME, $transactionId));

        $orderTransactionCollection = $this->orderTransactionRepository->search($criteria, $salesChannelContext->getContext());
        if ($orderTransactionCollection->count() === 0) {
            throw new Exception('Order transaction not found for CoinGate order #' . $transactionId);
        }

        return $orderTransactionCollection->first();
    }

    /**
     * @Route("/coingate-payment/webhook", name="storefront.coingate.webhook", methods={"POST","GET"})
     *
     * @param RequestDataBag $dataBag
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @return Response
     *
     * @throws Exception
     */
    public function index(RequestDataBag $dataBag, Request $request, SalesChannelContext $salesChannelContext): Response
    {
        try {
            $transactionId = $request->get('id', null);

            $client = $this->clientApiService->get($salesChannelContext);

            $order = $client->order->get($transactionId);

            $orderTransaction = $this->getOrderTransaction($transactionId, $salesChannelContext);

            switch ($order->status) {
                case 'paid':
                    $this->orderTransactionStateHandler->paid($orderTransaction->getId(), $salesChannelContext->getContext());
                    break;

                case 'canceled':
                case 'invalid':
                case 'expired':
                    $this->orderTransactionStateHandler->cancel($orderTransaction->getId(), $salesChannelContext->getContext());
                    break;
            }
        } catch (Exception $ex) {
            return new JsonResponse(['success' => false], 500);
        }

        return new JsonResponse(['success' => true], 200);
    }
}
