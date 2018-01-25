<?php
declare(strict_types=1);

namespace App\Controller;

use Payum\Bundle\PayumBundle\Controller\AuthorizeController as BundleAuthorizeController;

/**
 * Class AuthorizeController
 * @package App\Controller
 */
class AuthorizeController extends BundleAuthorizeController implements GatewayChooserInterface
{

}
