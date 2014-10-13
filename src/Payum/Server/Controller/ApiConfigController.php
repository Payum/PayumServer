<?php
namespace Payum\Server\Controller;

use Payum\Core\Bridge\Spl\ArrayObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Yaml\Yaml;

class ApiConfigController
{
    /**
     * @var array
     */
    private $currentConfig;
    /**
     * @var string
     */
    private $configFile;

    /**
     * @param array $currentConfig
     * @param string $configFile
     */
    public function __construct($currentConfig, $configFile)
    {
        $this->currentConfig = $currentConfig;
        $this->configFile = $configFile;
    }

    /**
     * @param Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        if ('json' !== $request->getContentType()) {
            throw new BadRequestHttpException('The request content type is invalid.');
        }

        $rawConfig = json_decode($request->getContent(), true);
        if (null ===  $rawConfig) {
            throw new BadRequestHttpException('The request content is not valid json.');
        }
        $rawConfig = ArrayObject::ensureArrayObject($rawConfig);

        if (false == $paymentName = $rawConfig['paymentName']) {
            throw new BadRequestHttpException('The paymentName is required.');
        }
        unset($rawConfig['paymentName']);

        if (false == $paymentFactory = $rawConfig['paymentFactory']) {
            throw new BadRequestHttpException('The paymentFactory is required.');
        }
        unset($rawConfig['paymentFactory']);

        $this->currentConfig['payments'][$paymentName][$paymentFactory] = (array) $rawConfig;

        file_put_contents($this->configFile, Yaml::dump($this->currentConfig));

        return new Response('', 204);
    }

    /**
     * @return JsonResponse
     */
    public function getAction()
    {
        return new JsonResponse(array(
            'payments' => array_keys($this->currentConfig['payments']),
        ));
    }
}
