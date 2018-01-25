<?php
declare(strict_types=1);

namespace App\Controller;

use Payum\Bundle\PayumBundle\Controller\CaptureController as BundleCaptureController;

/**
 * Class CaptureController
 * @package App\Controller
 */
class CaptureController extends BundleCaptureController implements GatewayChooserInterface
{

}
