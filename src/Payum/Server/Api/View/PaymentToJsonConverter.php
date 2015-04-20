<?php
namespace Payum\Server\Api\View;

use Payum\Core\Registry\GatewayRegistryInterface;
use Payum\Core\Request\GetHumanStatus;
use Payum\Server\Model\Payment;

class PaymentToJsonConverter
{
    /**
     * @var GatewayRegistryInterface
     */
    private $registry;

    /**
     * @param GatewayRegistryInterface $registry
     */
    public function __construct(GatewayRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param Payment $payment
     *
     * @return array
     */
    public function convert(Payment $payment)
    {
        $orderPayments = array();
        foreach ($payment->getPayments() as $orderPayment) {
            if (false == isset($orderPayment['status']) || GetHumanStatus::STATUS_UNKNOWN == $orderPayment['status']) {
                $payment = $this->registry->getGateway($orderPayment['name']);

                $payment->execute($status = new GetHumanStatus($orderPayment['details']));
                $orderPayment['status'] = $status->getValue();
            }

            $orderPayments[] = $orderPayment;
        }

        $links = [];
        foreach ($payment->getLinks() as $name => $link) {
            $links[$name] = ['href' => $link];
        }

        $payment->setPayments($orderPayments);

        return [
            'id' => $payment->getPublicId(),
            'gatewayName' => $payment->getGatewayName(),
            'afterUrl' => $payment->getAfterUrl(),
            'number' => $payment->getNumber(),
            'totalAmount' => $payment->getTotalAmount(),
            'currencyCode' => $payment->getCurrencyCode(),
            'clientEmail' => $payment->getClientEmail(),
            'clientId' => $payment->getClientId(),
            'description' => $payment->getDescription(),
            'payments' => $payment->getPayments(),
            '_links' => $links,
        ];
    }
}
