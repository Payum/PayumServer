<?php
declare(strict_types=1);

namespace Payum\Server\Schema\Controller;

use Payum\Server\Controller\ForwardExtensionTrait;
use Payum\Server\Schema\PaymentFormDefinitionBuilder;
use Payum\Server\Schema\PaymentSchemaBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;

class PaymentSchemaController
{
    use ForwardExtensionTrait;

    /**
     * @var PaymentSchemaBuilder
     */
    private $schemaBuilder;

    /**
     * @var PaymentFormDefinitionBuilder
     */
    private $formDefinitionBuilder;

    /**
     * @param PaymentSchemaBuilder $paymentSchemaBuilder
     * @param PaymentFormDefinitionBuilder $paymentFormDefinitionBuilder
     */
    public function __construct(
        PaymentSchemaBuilder $paymentSchemaBuilder,
        PaymentFormDefinitionBuilder $paymentFormDefinitionBuilder
    ) {
        $this->schemaBuilder = $paymentSchemaBuilder;
        $this->formDefinitionBuilder = $paymentFormDefinitionBuilder;
    }

    /**
     * @return JsonResponse
     */
    public function getNewAction() : JsonResponse
    {
        return new JsonResponse($this->schemaBuilder->buildNew(), 200, [
            'Content-Type' => 'application/schema+json',
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function getNewFormAction() : JsonResponse
    {
        return new JsonResponse($this->formDefinitionBuilder->buildNew());
    }
}
