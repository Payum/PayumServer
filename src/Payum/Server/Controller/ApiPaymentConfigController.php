<?php
namespace Payum\Server\Controller;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Security\Util\Mask;
use Payum\Server\Factory\Payment\FactoryInterface;
use Payum\Server\Factory\Payment\PaypalExpressCheckoutFactory;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Yaml\Yaml;

class ApiPaymentConfigController
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
     * @var FactoryInterface[]
     */
    private $factories;

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
     * @param FactoryInterface[] $factories
     * @param array $currentConfig
     * @param string $configFile
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator,
        $factories,
        $currentConfig,
        $configFile
    ) {
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->currentConfig = $currentConfig;
        $this->configFile = $configFile;
        $this->factories = $factories;
    }

    public function createAction($content)
    {
        $rawConfig = ArrayObject::ensureArrayObject($content);

        $builder = $this->formFactory->createNamedBuilder('', 'form', null, array(
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ));

        $builder
            ->add('name', 'text', array('constraints' => array(new NotBlank)))
            ->add('factory', 'text', array(
                'constraints' => array(
                    new NotBlank,
                    new Choice(array('choices' => array_keys($this->factories)))
                )
            ))
        ;

        $form = $builder->getForm();
        $form->submit((array) $rawConfig);

        if (!$form->isValid()) {
            return $this->normalizeInvalidForm($form);
        }

        $config = $form->getData();

        $builder
            ->add('options', 'form')
        ;

        $factory = $this->factories[$config['factory']];
        $factory->configureOptionsFormBuilder($builder->get('options'));

        $form = $builder->getForm();
        $form->submit((array) $rawConfig);
        if ($form->isValid()) {
            $config = $form->getData();

            $this->currentConfig['payments'][$config['name']]['factory'] = $factory->getName();
            $this->currentConfig['payments'][$config['name']]['options'] = $config['options'];

            file_put_contents($this->configFile, Yaml::dump($this->currentConfig, 5));

            return new Response('', 204, array(
                'Location' => $this->urlGenerator->generate('payment_config_get', array(
                    'name' => $config['name'],
                ), $absolute = true)
            ));
        }

        return $this->normalizeInvalidForm($form);
    }

    public function getAllAction()
    {
        $configs = array();
        foreach ($this->currentConfig['payments'] as $name => $config) {
            $configs[$name] = $this->normalizeConfig($name, $config);
        }

        return new JsonResponse(array('configs' => $configs));
    }

    public function getAction($name)
    {
        if (false == isset($this->currentConfig['payments'][$name])) {
            throw new NotFoundHttpException(sprintf('Config with name %s was not found.', $name));
        }

        return new JsonResponse(array('config' => $this->normalizeConfig($name, $this->currentConfig['payments'][$name])));
    }

    protected function normalizeConfig($name, array $config)
    {
        $options = array();
        foreach ($config['options'] as $optionName => $optionValue) {
            $options[$optionName] = Mask::mask($optionValue, '*');
        }

        return array(
            'name' => $name,
            'factory' => $config['factory'],
            'options' => $options,
        );
    }

    protected function normalizeInvalidForm(FormInterface $form)
    {
        return new JsonResponse(array(
            'errors' => $form->getErrorsAsString(),
        ));
    }
}
