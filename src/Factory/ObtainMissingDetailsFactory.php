<?php
declare(strict_types=1);

namespace App\Factory;

use App\Action\ObtainMissingDetailsAction;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\Options;

/**
 * Class ObtainMissingDetailsFactory
 * @package App\Factory
 */
class ObtainMissingDetailsFactory
{
    /**
     * @param ContainerInterface $container
     * @param $formFactory
     *
     * @return callable
     */
    public static function create(ContainerInterface $container, $formFactory) : callable
    {
        return function (Options $options) use ($container, $formFactory) {
            return new ObtainMissingDetailsAction(
                $formFactory,
                $container->getParameter('payum.template.obtain_missing_details')
            );
        };
    }
}
