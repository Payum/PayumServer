<?php
declare(strict_types=1);

namespace App\Schema\Controller;

use App\Controller\ForwardExtensionTrait;
use App\Schema\TokenSchemaBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class TokenSchemaController
 * @package App\Schema\Controller
 */
class TokenSchemaController
{
    use ForwardExtensionTrait;

    /**
     * @var TokenSchemaBuilder
     */
    private $schemaBuilder;

    /**
     * @param TokenSchemaBuilder $tokenSchemaBuilder
     */
    public function __construct(TokenSchemaBuilder $tokenSchemaBuilder)
    {
        $this->schemaBuilder = $tokenSchemaBuilder;
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
}
