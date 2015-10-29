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
        $links = [];
        foreach ($payment->getLinks() as $name => $link) {
            $links[$name] = ['href' => $link];
        }

        return [
            'id' => $payment->getPublicId(),
            'gatewayName' => $payment->getGatewayName(),
            'number' => $payment->getNumber(),
            'totalAmount' => $payment->getTotalAmount(),
            'currencyCode' => $payment->getCurrencyCode(),
            'clientEmail' => $payment->getClientEmail(),
            'clientId' => $payment->getClientId(),
            'description' => $payment->getDescription(),
            'details' => $payment->getDetails(),
            '_links' => $links,
        ];
    }
}
