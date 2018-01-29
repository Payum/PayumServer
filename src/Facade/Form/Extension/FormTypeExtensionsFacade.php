<?php
declare(strict_types=1);

namespace App\Facade\Form\Extension;

use App\Form\Extension\CreditCardExtension;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\TokenStorage\NativeSessionTokenStorage;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;

/**
 * Class FormTypeExtensionsFacade
 * @package App\Facade\Form\Extension
 */
class FormTypeExtensionsFacade
{
    /**
     * @param ContainerInterface $container
     *
     * @return array
     */
    public static function get(ContainerInterface $container) : array
    {
        $extensions[] = self::getFormCsrfExtension($container);
        $extensions[] = new HttpFoundationExtension();

        if ($container->has('validator')) {
            $extensions[] = new FormTypeValidatorExtension($container->get('validator'));
        }

        $extensions[] = new CreditCardExtension();

        return $extensions;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return CsrfExtension
     */
    private static function getFormCsrfExtension(ContainerInterface $container) : CsrfExtension
    {
        $storage = $container->has('session') ? new SessionTokenStorage($container->get('session')) : new NativeSessionTokenStorage();
        $formCsrfProvider = new CsrfTokenManager(null, $storage);

        if ($container->has('translator')) {
            $formCsrfExtension = new CsrfExtension($formCsrfProvider, $container->get('translator'));
        } else {
            $formCsrfExtension = new CsrfExtension($formCsrfProvider);
        }

        return $formCsrfExtension;
    }
}
