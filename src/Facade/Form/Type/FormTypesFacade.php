<?php
declare(strict_types=1);

namespace App\Facade\Form\Type;

use App\Form\Type\ChooseGatewayType;
use Payum\Core\Payum;
use Psr\Container\ContainerInterface;
use Payum\Core\Bridge\Symfony\Form\Type\CreditCardExpirationDateType;
use Payum\Core\Bridge\Symfony\Form\Type\CreditCardType;
use Payum\Core\Bridge\Symfony\Form\Type\GatewayConfigType;
use Payum\Core\Bridge\Symfony\Form\Type\GatewayFactoriesChoiceType;
use Payum\Core\Bridge\Symfony\Form\Type\GatewayChoiceType;

/**
 * Class FormTypesFacade
 * @package App\Facade\Form\Extension
 */
class FormTypesFacade
{
    /**
     * @param ContainerInterface $container
     *
     * @return array
     */
    public static function get(ContainerInterface $container) : array
    {
        $types[] = new ChooseGatewayType(function () use ($container) {
            /** @var Payum $payum */
            $payum = $container->get('payum');

            $choices = [];
            foreach ($payum->getGateways() as $name => $gateway) {
                $choices[ucwords(str_replace(['_'], ' ', $name))] = $name;
            }

            return $choices;
        });

        $types[] = new CreditCardType();
        $types[] = new CreditCardExpirationDateType();
        $types[] = new GatewayFactoriesChoiceType($container->get('payum.gateway_factory_choices_callback'));
        $types[] = new GatewayChoiceType($container->get('payum.gateway_choices_callback'));
        $types[] = new GatewayConfigType($container->get('payum'));

        return $types;
    }
}
