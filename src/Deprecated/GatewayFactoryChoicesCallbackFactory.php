<?php
declare(strict_types=1);

namespace App\Factory;

use Payum\Core\Payum;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\Options;

/**
 * Class GatewayFactoryChoicesCallbackFactory
 * @package App\Factory
 */
class GatewayFactoryChoicesCallbackFactory
{
//    /**
//     * @param ContainerInterface $container
//     *
//     * @return callable
//     */
//    public static function create(ContainerInterface $container) : callable
//    {
//        return function (Options $options) use ($container) {
//            /** @var Payum $payum */
//            $payum = $container->get('payum');
//
//            $choices = [];
//            foreach ($payum->getGatewayFactories() as $name => $factory) {
//                if (in_array($name, ['omnipay', 'omnipay_direct', 'omnipay_offsite'])) {
//                    continue;
//                }
//
//                $choices[ucwords(str_replace(['_', 'omnipay'], ' ', $name))] = $name;
//            }
//
//            return $choices;
//        };
//    }
}
