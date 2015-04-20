<?php
namespace Payum\Server\Api\Controller;

use Payum\Core\Registry\GatewayFactoryRegistryInterface;
use Payum\Server\Api\View\FormToJsonConverter;
use Payum\Server\Model\GatewayConfig;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class GatewayMetaController
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var FormToJsonConverter
     */
    private $formToJsonConverter;

    /**
     * @var GatewayFactoryRegistryInterface
     */
    private $registry;

    /**
     * @param FormFactoryInterface $formFactory
     * @param FormToJsonConverter $formToJsonConverter
     * @param GatewayFactoryRegistryInterface $registry
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        FormToJsonConverter $formToJsonConverter,
        GatewayFactoryRegistryInterface $registry
    ) {
        $this->formFactory = $formFactory;
        $this->formToJsonConverter = $formToJsonConverter;
        $this->registry = $registry;
    }

    /**
     * @return JsonResponse
     */
    public function getAllAction()
    {
        $normalizedFactories = [];

        foreach ($this->registry->getGatewayFactories() as $name => $factory) {
            $gatewayConfig = new GatewayConfig();
            $gatewayConfig->setFactoryName($name);

            $form = $this->formFactory->create('payum_gateway_config', $gatewayConfig, [
                'data_class' => GatewayConfig::class,
            ]);

            $normalizedFactories[$name] = $this->formToJsonConverter->convertMeta($form);
            $normalizedFactories[$name]['config'] = $this->formToJsonConverter->convertMeta($form->get('config'));
        }

        return new JsonResponse(array(
            'metas' => $normalizedFactories,
        ));
    }
}
