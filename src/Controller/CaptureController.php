<?php
declare(strict_types=1);

namespace App\Controller;

use Payum\Bundle\PayumBundle\Controller\CaptureController as BundleCaptureController;

class CaptureController extends BundleCaptureController implements GatewayChooserInterface
{

}
