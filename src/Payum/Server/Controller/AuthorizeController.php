<?php
declare(strict_types=1);

namespace Payum\Server\Controller;

use Payum\Bundle\PayumBundle\Controller\AuthorizeController as BundleAuthorizeController;

/**
 * Class AuthorizeController
 * @package Payum\Server\Controller
 */
class AuthorizeController extends BundleAuthorizeController implements GatewayChooserInterface
{

}
