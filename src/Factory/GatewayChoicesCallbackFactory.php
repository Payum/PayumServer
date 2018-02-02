<?php
declare(strict_types=1);

namespace App\Factory;

use App\Storage\GatewayConfigStorage;
use Payum\Core\Model\GatewayConfigInterface;
use App\Util\StringUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GatewayChoicesCallbackFactory
{
    public static function createCallback(ContainerInterface $container) : callable
    {
        return function () use ($container) : array {
            return self::createArray($container);
        };
    }

    public static function createArray(ContainerInterface $container) : array
    {
        $choices = [];

        /** @var GatewayConfigStorage $gatewayConfigStorage */
        $gatewayConfigStorage = $container->get(GatewayConfigStorage::class);
        foreach ($gatewayConfigStorage->find([]) as $config) {
            /** @var GatewayConfigInterface $config */
            $choices[StringUtil::nameToTitle($config->getGatewayName())] = $config->getGatewayName();
        }

        return $choices;
    }
}
