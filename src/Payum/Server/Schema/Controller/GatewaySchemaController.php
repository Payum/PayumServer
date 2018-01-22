<?php
declare(strict_types=1);

namespace Payum\Server\Schema\Controller;

use Payum\Server\Controller\ForwardExtensionTrait;
use Payum\Server\Schema\GatewayFormDefinitionBuilder;
use Payum\Server\Schema\GatewaySchemaBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class GatewaySchemaController
 * @package Payum\Server\Schema\Controller
 */
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
    public function __construct(
        GatewaySchemaBuilder $schemaBuilder,
        GatewayFormDefinitionBuilder $formDefinitionBuilder
    ) {
        $this->schemaBuilder = $schemaBuilder;
        $this->formDefinitionBuilder = $formDefinitionBuilder;
    }

    /**
     * @return JsonResponse
     */
    public function getDefaultAction() : JsonResponse
    {
        return new JsonResponse($this->schemaBuilder->buildDefault(), 200, [
            'Content-Type' => 'application/schema+json',
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function getDefaultFormAction() : JsonResponse
    {
        return new JsonResponse($this->formDefinitionBuilder->buildDefault());
    }

    /**
     *
     * @param string $name
     *
     * @return JsonResponse
     */
    public function getAction(string $name) : JsonResponse
    {
        return new JsonResponse($this->schemaBuilder->build($name), 200, [
            'Content-Type' => 'application/schema+json',
        ]);
    }

    /**
     * @param string $name
     *
     * @return JsonResponse
     */
    public function getFormAction(string $name) : JsonResponse
    {
        return new JsonResponse($this->formDefinitionBuilder->build($name));
    }
}
