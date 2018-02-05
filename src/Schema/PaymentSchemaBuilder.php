<?php
declare(strict_types=1);

namespace App\Schema;

use App\Storage\GatewayConfigStorage;
use Payum\ISO4217\Currency;
use Payum\ISO4217\ISO4217;
use App\Model\GatewayConfig;
use App\Util\StringUtil;

class PaymentSchemaBuilder
{
    /**
     * @var GatewayConfigStorage
     */
    private $gatewayConfigStorage;

    public function __construct(GatewayConfigStorage $gatewayConfigStorage)
    {
        $this->gatewayConfigStorage = $gatewayConfigStorage;
    }

    public function buildNew()
    {
        $gateways = iterator_to_array($this->gatewayConfigStorage->find([]));
        $enum = array_map(function (GatewayConfig $gatewayConfig) {
            return $gatewayConfig->getGatewayName();
        }, $gateways);

        $currencyCodes = array_map(function (Currency $currency) {
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
                    'title' => StringUtil::nameToTitle('currencyCode'),
                ],
                'clientEmail' => (object) [
                    'type' => 'string',
                    'title' => StringUtil::nameToTitle('clientEmail'),
                ],
                'clientId' => (object) [
                    'type' => ['string', 'numeric'],
                    'title' => StringUtil::nameToTitle('clientId'),
                ],
                'description' => (object) [
                    'type' => 'string',
                    'title' => StringUtil::nameToTitle('description'),
                ],
            ],
            "required" => ["currencyCode", "totalAmountInput"],
        ];
    }
}
