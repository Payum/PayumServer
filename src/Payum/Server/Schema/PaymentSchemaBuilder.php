<?php
namespace Payum\Server\Schema;

use Payum\Core\Payum;
use Payum\ISO4217\Currency;
use Payum\ISO4217\ISO4217;
use Payum\Server\Model\GatewayConfig;
use Payum\Server\Storage\YadmStorage;
use Payum\Server\Util\StringUtil;

class PaymentSchemaBuilder
{
    /**
     * @var YadmStorage
     */
    private $gatewayConfigStorage;

    /**
     * @param YadmStorage $gatewayConfigStorage
     * @internal param Payum $payum
     */
    public function __construct(YadmStorage $gatewayConfigStorage)
    {
        $this->gatewayConfigStorage = $gatewayConfigStorage;
    }

    /**
     * @return object
     */
    public function buildNew()
    {
        $enum = array_map(function(GatewayConfig $gatewayConfig) {
            return $gatewayConfig->getGatewayName();
        }, $this->gatewayConfigStorage->findBy([]));


        $currencyCodes = array_map(function(Currency $currency) {
            return $currency->getAlpha3();
        }, (new ISO4217())->findAll());

        return (object) [
            '$schema' => 'http://json-schema.org/schema#',
            'type' => 'object',
            'properties' => (object) [
                'gatewayName' => (object) [
                    'type' => 'string',
                    'enum' => $enum,
                    'title' => StringUtil::nameToTitle('gatewayName'),
                ],
                'totalAmountInput' => (object) [
                    'type' => 'number',
                    'title' => 'Amount',
                    'minimum' => 0,
                    'exclusiveMinimum' => true,
                ],
                'currencyCode' => (object) [
                    'type' => 'string',
                    'enum' => $currencyCodes,
                    'title' => StringUtil::nameToTitle('currencyCode')
                ],
                'clientEmail' => (object) [
                    'type' => 'string',
                    'title' => StringUtil::nameToTitle('clientEmail')
                ],
                'clientId' => (object) [
                    'type' => ['string', 'numeric'],
                    'title' => StringUtil::nameToTitle('clientId')
                ],
                'description' => (object) [
                    'type' => 'string',
                    'title' => StringUtil::nameToTitle('description')
                ],
            ],
            "required" => [ "currencyCode", "totalAmountInput"]
        ];
    }
}
