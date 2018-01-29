<?php
declare(strict_types=1);

namespace App\Schema;

use Payum\Core\GatewayFactoryInterface;
use Payum\Core\Payum;
use App\Util\StringUtil;

/**
 * Class GatewayFormDefinitionBuilder
 * @package App\Schema
 */
class GatewayFormDefinitionBuilder
{
    /**
     * @var Payum
     */
    private $payum;

    /**
     * @param Payum $payum
     */
    public function __construct(Payum $payum)
    {
        $this->payum = $payum;
    }

    /**
     * @return array
     */
    public function buildDefault() : array
    {
        $titleMap = [];

        /**
         * @var string $name
         * @var GatewayFactoryInterface $factory
         */
        foreach ($this->payum->getGatewayFactories() as $name => $factory) {
            if ('core' === $name) {
                continue;
            }

            $config = $factory->createConfig();

            $title = isset($config['payum.factory_title']) ? $config['payum.factory_title'] : ucwords(StringUtil::nameToTitle($name));

            $group = 'Others';
            if (false !== strpos($name, 'omnipay')) {
                $group = 'Omnipay';
            } elseif (false !== strpos($name, 'paypal')) {
                $group = 'Paypal';
            } elseif (false !== strpos($name, 'be2bill')) {
                $group = 'Be2bill';
            } elseif (false !== strpos($name, 'klarna')) {
                $group = 'Klarna';
            } elseif (false !== strpos($name, 'stripe')) {
                $group = 'Stripe';
            }

            $titleMap[] = ['name' => $title, 'value' => $name, 'group' => $group];
        }

        return [
            'gatewayName',
            [
                "key" => "factoryName",
                "type" => "select",
                "titleMap" => $titleMap,
            ],
        ];
    }

    /**
     * @param $name
     *
     * @return array
     */
    public function build($name) : array
    {
        $definition = $this->buildDefault();

        $config = $this->payum->getGatewayFactory($name)->createConfig();
        if (isset($config['payum.default_options'])) {
            foreach ($config['payum.default_options'] as $name => $value) {
                $definition[] = 'config.' . $name;
            }
        }

        $definition[] = [
            'type' => 'submit',
            'title' => 'Create',
        ];

        return $definition;
    }
}
