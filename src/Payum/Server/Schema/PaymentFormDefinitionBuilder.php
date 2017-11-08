<?php
namespace Payum\Server\Schema;

use Payum\Core\Storage\StorageInterface;
use Payum\ISO4217\Currency;
use Payum\ISO4217\ISO4217;
use Payum\Server\Model\GatewayConfig;
use Payum\Server\Util\StringUtil;

class PaymentFormDefinitionBuilder
{
    /**
     * @var StorageInterface
     */
    private $gatewayConfigStorage;

    /**
     * @param StorageInterface $gatewayConfigStorage
     * @internal param Payum $payum
     */
    public function __construct(StorageInterface $gatewayConfigStorage)
    {
        $this->gatewayConfigStorage = $gatewayConfigStorage;
    }

    /**
     * @return array
     */
    public function buildNew()
    {
        $titleMap = array_map(function(GatewayConfig $gatewayConfig) {
            return [
                'name' => StringUtil::nameToTitle($gatewayConfig->getGatewayName()),
                'value' => $gatewayConfig->getGatewayName(),
            ];
        }, $this->gatewayConfigStorage->findBy([]));

        $currencyMap = array_map(function(Currency $currency) {
            return [
                'name' => $currency->getName(),
                'value' => $currency->getAlpha3(),
                'group' => in_array($currency->getAlpha3(), ['USD', 'EUR', 'GBP', 'JPY', 'CNY']) ? 'Popular' : 'Other',
            ];
        }, (new ISO4217())->findAll());


        usort($currencyMap, function(array $left, array $right) {
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
                "titleMap" => $currencyMap
            ],
            [
                "key" => "gatewayName",
                "type" => "select",
                "titleMap" => $titleMap
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
            ]
        ];
    }
}
