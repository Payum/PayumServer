<?php
declare(strict_types=1);

namespace App\Schema\Controller;

use App\Controller\ForwardExtensionTrait;
use App\Schema\PaymentFormDefinitionBuilder;
use App\Schema\PaymentSchemaBuilder;
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

    public function __construct(
        PaymentSchemaBuilder $paymentSchemaBuilder,
        PaymentFormDefinitionBuilder $paymentFormDefinitionBuilder
    ) {
        $this->schemaBuilder = $paymentSchemaBuilder;
        $this->formDefinitionBuilder = $paymentFormDefinitionBuilder;
    }

    public function getNewAction() : JsonResponse
    {
        return new JsonResponse($this->schemaBuilder->buildNew(), 200, [
            'Content-Type' => 'application/schema+json',
        ]);
    }

    public function getNewFormAction() : JsonResponse
    {
        return new JsonResponse($this->formDefinitionBuilder->buildNew());
    }
}
