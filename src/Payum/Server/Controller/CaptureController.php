<?php
declare(strict_types=1);

namespace Payum\Server\Controller;

use Payum\Bundle\PayumBundle\Controller\CaptureController as BundleCaptureController;

/**
 * Class CaptureController
 * @package Payum\Server\Controller
 */
class CaptureController extends BundleCaptureController implements GatewayChooserInterface
{

}
