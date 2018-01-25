<?php
declare(strict_types=1);

namespace App\Factory;

use Makasim\Yadm\Storage;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Payum;
use App\Util\StringUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\Options;

/**
 * Class GatewayChoicesCallbackFactory
 * @package App\Factory
 */
class GatewayChoicesCallbackFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return callable
     */
    public static function create(ContainerInterface $container) : callable
    {
        return function (Options $options) use ($container) {
            /** @var Payum $payum */
            $payum = $container->get('payum');

            $choices = [];
            foreach ($payum->getGateways() as $name => $gateway) {
                $choices[ucwords(str_replace(['_'], ' ', $name))] = $name;
            }

            $choices = call_user_func($choices, $options);

            /** @var Storage $gatewayConfigStorage */
            $gatewayConfigStorage = $container->get('payum.gateway_config_storage');
            foreach ($gatewayConfigStorage->find([]) as $config) {
                /** @var GatewayConfigInterface $config */
                $choices[StringUtil::nameToTitle($config->getGatewayName())] = $config->getGatewayName();
            }

            return $choices;
        };
    }
}
