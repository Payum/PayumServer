<?php
namespace Payum\Server\Schema\Controller;

use Payum\Server\Controller\ForwardExtensionTrait;
use Payum\Server\Schema\GatewayFormDefinitionBuilder;
use Payum\Server\Schema\GatewaySchemaBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;

class GatewaySchemaController
{
    use ForwardExtensionTrait;

    /**
     * @var GatewaySchemaBuilder
     */
    private $schemaBuilder;

    /**
     * @var GatewayFormDefinitionBuilder
     */
    private $formDefinitionBuilder;

    /**
     * @param GatewaySchemaBuilder $schemaBuilder
     * @param GatewayFormDefinitionBuilder $formDefinitionBuilder
     */
    public function __construct(GatewaySchemaBuilder $schemaBuilder, GatewayFormDefinitionBuilder $formDefinitionBuilder)
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
