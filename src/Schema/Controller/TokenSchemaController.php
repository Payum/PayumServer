<?php
declare(strict_types=1);

namespace App\Schema\Controller;

use App\Controller\ForwardExtensionTrait;
use App\Schema\TokenSchemaBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;

class TokenSchemaController
{
    use ForwardExtensionTrait;

    /**
     * @var TokenSchemaBuilder
     */
    private $schemaBuilder;

    public function __construct(TokenSchemaBuilder $tokenSchemaBuilder)
    {
        $this->schemaBuilder = $tokenSchemaBuilder;
    }

    public function getNewAction() : JsonResponse
    {
        return new JsonResponse($this->schemaBuilder->buildNew(), 200, [
            'Content-Type' => 'application/schema+json',
        ]);
    }
}
