<?php
namespace Payum\Server\Controller;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Server\Api\View\FormToJsonConverter;
use Payum\Server\Factory\Storage\FactoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Yaml\Yaml;

class ApiStorageConfigController
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var FormToJsonConverter
     */
    private $formToJsonConverter;

    /**
     * @var array
     */
    private $currentConfig;

    /**
     * @var string
     */
    private $configFile;

    /**
     * @param FormFactoryInterface $formFactory
     * @param UrlGeneratorInterface $urlGenerator
     * @param FormToJsonConverter $formToJsonConverter
     * @param array $currentConfig
     * @param string $configFile
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator,
        FormToJsonConverter $formToJsonConverter,
        $currentConfig,
        $configFile
    ) {
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->currentConfig = $currentConfig;
        $this->configFile = $configFile;
        $this->formToJsonConverter = $formToJsonConverter;
    }

    public function updateOrderAction($content)
    {
        return $this->doUpdate('order', $content);
    }

    public function updateTokenAction($content)
    {
        return $this->doUpdate('security_token', $content);
    }

    public function getAllAction()
    {
        $configs = array();
        foreach ($this->currentConfig['storages'] as $name => $config) {
            $configs[$name] = $this->normalizeConfig($name, $config);
        }

        return new JsonResponse(array('storages' => $configs));
    }

    public function getAction($name)
    {
        if (false == isset($this->currentConfig['storages'][$name])) {
            throw new NotFoundHttpException(sprintf('Config with name %s was not found.', $name));
        }

        return new JsonResponse(array('storage' => $this->normalizeConfig($name, $this->currentConfig['storages'][$name])));
    }

    public function doUpdate($name, $content)
    {
        $rawConfig = ArrayObject::ensureArrayObject($content);

        $form = $this->formFactory->create('create_storage_config');
        $form->submit((array) $rawConfig);
        if (false == $form->isValid()) {
            return new JsonResponse($this->formToJsonConverter->convertInvalid($form), 400);
        }
        $config = $form->getData();

        $form = $this->formFactory->create('create_storage_config', null, array(
            'factory' => $config['factory'],
        ));
        $form->submit((array) $rawConfig);
        if ($form->isValid()) {
            $config = $form->getData();

            $defaultConfig = array(
                'order' => array(
                    'modelClass' => 'Payum\Server\Model\Order',
                    'idProperty' => 'number',
                    'factory' => $config['factory'],
                ),
                'security_token' => array(
                    'modelClass' => 'Payum\Server\Model\SecurityToken',
                    'idProperty' => 'hash',
                    'factory' => $config['factory'],
                ),
            );

            $this->currentConfig['storages'][$name] = $defaultConfig[$name];
            $this->currentConfig['storages'][$name]['options'] = $config['options'];

            file_put_contents($this->configFile, Yaml::dump($this->currentConfig, 5));

            return new JsonResponse(array('storage' => $this->normalizeConfig($name, $this->currentConfig['storages'][$name])));
        }

        return new JsonResponse($this->formToJsonConverter->convertInvalid($form), 400);
    }

    /**
     * @param $name
     * @param array $config
     *
     * @return array
     */
    protected function normalizeConfig($name, array $config)
    {
        $config['name'] = $name;

        return $config;
    }
}
