<?php
namespace Payum\Server\Controller;

use Payum\Core\Bridge\Spl\ArrayObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Yaml\Yaml;

class ApiPaymentConfigController
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

        if (false == $name = $rawConfig['name']) {
            throw new BadRequestHttpException('The name is required.');
        }

        if (false == $factory = $rawConfig['factory']) {
            throw new BadRequestHttpException('The factory is required.');
        }

        $this->currentConfig['payments'][$name]['factory'] = $factory;
        $this->currentConfig['payments'][$name]['options'] = $rawConfig['options'] ?: array();;

        file_put_contents($this->configFile, Yaml::dump($this->currentConfig, 5));

        return new Response('', 204);
    }

    /**
     * @return JsonResponse
     */
    public function getAction()
    {
        return new JsonResponse(array($this->currentConfig));
    }
}
