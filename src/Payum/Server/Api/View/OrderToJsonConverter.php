<?php
namespace Payum\Server\Api\View;

use Payum\Core\Registry\PaymentRegistryInterface;
use Payum\Core\Request\GetHumanStatus;
use Payum\Server\Model\Order;

class OrderToJsonConverter
{
    /**
     * @var PaymentRegistryInterface
     */
    private $registry;

    /**
     * @param PaymentRegistryInterface $registry
     */
    public function __construct(PaymentRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    public function convert(Order $order)
    {

        $orderPayments = array();
        foreach ($order->getPayments() as $orderPayment) {
            if (false == isset($orderPayment['status']) || GetHumanStatus::STATUS_UNKNOWN == $orderPayment['status']) {
                $payment = $this->registry->getPayment($orderPayment['name']);

                $payment->execute($status = new GetHumanStatus($orderPayment['details']));
                $orderPayment['status'] = $status->getValue();
            }

            $orderPayments[] = $orderPayment;
        }

        $order->setPayments($orderPayments);

        return array(
            'number' => $order->getNumber(),
            'totalAmount' => $order->getTotalAmount(),
            'currencyCode' => $order->getCurrencyCode(),
            'clientEmail' => $order->getClientEmail(),
            'clientId' => $order->getClientId(),
            'payments' => $order->getPayments(),
        );
    }
}