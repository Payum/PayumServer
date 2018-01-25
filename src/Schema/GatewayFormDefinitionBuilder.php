<?php
namespace App\Schema;

use Payum\Core\Payum;
use App\Util\StringUtil;

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
    public function buildDefault()
    {
        $titleMap = [];

        foreach ($this->payum->getGatewayFactories() as $name => $factory) {
            if ('core' == $name) {
                continue;
            }

            $config = $factory->createConfig();

            $title = isset($config['payum.factory_title']) ? $config['payum.factory_title'] : ucwords(StringUtil::nameToTitle($name));

            $group = 'Others';
            if (false !== strpos($name, 'omnipay')) {
                $group = 'Omnipay';
            } else if (false !== strpos($name, 'paypal')) {
                $group = 'Paypal';
            } else if (false !== strpos($name, 'be2bill')) {
                $group = 'Be2bill';
            } else if (false !== strpos($name, 'klarna')) {
                $group = 'Klarna';
            } else if (false !== strpos($name, 'stripe')) {
                $group = 'Stripe';
            }

            $titleMap[] = ['name' => $title, 'value' => $name, 'group' => $group];
        }

        return [
            'gatewayName',
            [
                "key" => "factoryName",
                "type" => "select",
                "titleMap" => $titleMap
            ],
        ];
    }

    /**
     * @param $name
     *
     * @return array
     */
    public function build($name)
    {
        $definition = $this->buildDefault();

        $config = $this->payum->getGatewayFactory($name)->createConfig();
        if (isset($config['payum.default_options'])) {
            foreach ($config['payum.default_options'] as $name => $value) {
                $definition[] = 'config.'.$name;
            }
        }

        $definition[] = [
            'type' => 'submit',
            'title' => 'Create',
        ];

        return $definition;
    }
}
