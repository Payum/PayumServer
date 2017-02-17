<?php
namespace Payum\Server\Schema\Controller;

use Payum\Server\Controller\ForwardExtensionTrait;
use Payum\Server\Schema\FormDefinitionBuilder;
use Payum\Server\Schema\SchemaBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;

class GatewaySchemaController
{
    use ForwardExtensionTrait;

    /**
     * @var SchemaBuilder
     */
    private $schemaBuilder;

    /**
     * @var FormDefinitionBuilder
     */
    private $formDefinitionBuilder;

    /**
     * @param SchemaBuilder $schemaBuilder
     * @param FormDefinitionBuilder $formDefinitionBuilder
     */
    public function __construct(SchemaBuilder $schemaBuilder, FormDefinitionBuilder $formDefinitionBuilder)
    {
        $this->schemaBuilder = $schemaBuilder;
        $this->formDefinitionBuilder = $formDefinitionBuilder;
    }

    /**
     * @return JsonResponse
     */
    public function getDefaultAction()
    {
        return new JsonResponse($this->schemaBuilder->buildDefault(), 200, [
            'Content-Type' => 'application/schema+json',
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function getDefaultFormAction()
    {
        return new JsonResponse($this->formDefinitionBuilder->buildDefault());
    }

    /**
     * @return JsonResponse
     */
    public function getAction($name)
    {
        return new JsonResponse($this->schemaBuilder->build($name), 200, [
            'Content-Type' => 'application/schema+json',
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function getFormAction($name)
    {
        return new JsonResponse($this->formDefinitionBuilder->build($name));
    }
}
