<?php
declare(strict_types=1);

namespace App\Schema\Controller;

use App\Controller\ForwardExtensionTrait;
use App\Schema\GatewayFormDefinitionBuilder;
use App\Schema\GatewaySchemaBuilder;
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

    public function __construct(
        GatewaySchemaBuilder $schemaBuilder,
        GatewayFormDefinitionBuilder $formDefinitionBuilder
    ) {
        $this->schemaBuilder = $schemaBuilder;
        $this->formDefinitionBuilder = $formDefinitionBuilder;
    }

    public function getDefaultAction() : JsonResponse
    {
        return new JsonResponse($this->schemaBuilder->buildDefault(), 200, [
            'Content-Type' => 'application/schema+json',
        ]);
    }

    public function getDefaultFormAction() : JsonResponse
    {
        return new JsonResponse($this->formDefinitionBuilder->buildDefault());
    }

    public function getAction(string $name) : JsonResponse
    {
        return new JsonResponse($this->schemaBuilder->build($name), 200, [
            'Content-Type' => 'application/schema+json',
        ]);
    }

    public function getFormAction(string $name) : JsonResponse
    {
        return new JsonResponse($this->formDefinitionBuilder->build($name));
    }
}
