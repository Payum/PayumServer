<?php
declare(strict_types=1);

namespace App\Controller;

use Payum\Bundle\PayumBundle\Controller\AuthorizeController as BundleAuthorizeController;

class AuthorizeController extends BundleAuthorizeController implements GatewayChooserInterface
{

}
