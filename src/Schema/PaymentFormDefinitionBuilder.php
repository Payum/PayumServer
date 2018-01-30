<?php
declare(strict_types=1);

namespace App\Schema;

use App\Storage\GatewayConfigStorage;
use Payum\ISO4217\Currency;
use Payum\ISO4217\ISO4217;
use App\Model\GatewayConfig;
use App\Util\StringUtil;

/**
 * Class PaymentFormDefinitionBuilder
 * @package App\Schema
 */
class PaymentFormDefinitionBuilder
{
    /**
     * @var GatewayConfigStorage
     */
    private $gatewayConfigStorage;

    /**
     * @param GatewayConfigStorage $gatewayConfigStorage
     *
     * @internal param Payum $payum
     */
    public function __construct(GatewayConfigStorage $gatewayConfigStorage)
    {
        $this->gatewayConfigStorage = $gatewayConfigStorage;
    }

    /**
     * @return array
     */
    public function buildNew()
    {
        $gateways = iterator_to_array($this->gatewayConfigStorage->find([]));
        $titleMap = array_map(function (GatewayConfig $gatewayConfig) {
            return [
                'name' => StringUtil::nameToTitle($gatewayConfig->getGatewayName()),
                'value' => $gatewayConfig->getGatewayName(),
            ];
        }, $gateways);

        $currencyMap = array_map(function (Currency $currency) {
            return [
                'name' => $currency->getName(),
                'value' => $currency->getAlpha3(),
                'group' => in_array($currency->getAlpha3(), ['USD', 'EUR', 'GBP', 'JPY', 'CNY']) ? 'Popular' : 'Other',
            ];
        }, (new ISO4217())->findAll());


        usort($currencyMap, function (array $left, array $right) {
            if ('Popular' == $left['group'] && 'Popular' == $right['group']) {
                return 0;
            }

            if ('Popular' == $left['group'] && 'Popular' != $right['group']) {
                return -1;
            }

            return 1;
        });

        return [
            'totalAmountInput',
            [
                "key" => 'currencyCode',
                "type" => "select",
                "titleMap" => $currencyMap,
            ],
            [
                "key" => "gatewayName",
                "type" => "select",
                "titleMap" => $titleMap,
            ],
            'clientEmail',
            'clientId',
            [
                'key' => 'description',
                'type' => 'textarea',
            ],
            [
                'type' => 'submit',
                'title' => 'Create',
            ],
        ];
    }
}
