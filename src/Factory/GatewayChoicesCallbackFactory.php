<?php
declare(strict_types=1);

namespace App\Factory;

use App\Model\GatewayConfig;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Payum;
use App\Util\StringUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        return function () use ($container) : array {
            /** @var Payum $payum */
            $payum = $container->get('payum');

            $choices = [];
            foreach ($payum->getGateways() as $name => $gateway) {
                $choices[ucwords(str_replace(['_'], ' ', $name))] = $name;
            }

            $gatewayConfigStorage = $payum->getStorage(GatewayConfig::class);
            foreach ($gatewayConfigStorage->findBy([]) as $config) {
                /** @var GatewayConfigInterface $config */
                $choices[StringUtil::nameToTitle($config->getGatewayName())] = $config->getGatewayName();
            }

            return $choices;
        };
    }
}
