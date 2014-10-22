<?php
namespace Payum\Server\Controller;

use Payum\Server\Api\View\FormToJsonConverter;
use Payum\Server\Factory\Storage\FactoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiStorageMetaController
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
     * @var array|FactoryInterface[]
     */
    private $factories;

    /**
     * @param FormFactoryInterface $formFactory
     * @param FormToJsonConverter $formToJsonConverter
     * @param FactoryInterface[] $factories
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        FormToJsonConverter $formToJsonConverter,
        array $factories
    ) {
        $this->formFactory = $formFactory;
        $this->formToJsonConverter = $formToJsonConverter;
        $this->factories = $factories;
    }

    /**
     * @return JsonResponse
     */
    public function getAllAction()
    {
        $normalizedFactories = array();

        foreach ($this->factories as $name => $factory) {
            $builder = $this->formFactory->createBuilder('form', null, array(
                'csrf_protection' => false,
            ));

            $factory->configureOptionsFormBuilder($builder);

            $normalizedFactories[$name] = array(
                'options' => $this->formToJsonConverter->convertMeta($builder->getForm())
            );
        }

        $builder = $this->formFactory->createBuilder('create_storage_config');

        return new JsonResponse(array(
            'metas' => $normalizedFactories,
            'generic' => $this->formToJsonConverter->convertMeta($builder->getForm()),
        ));
    }
}
