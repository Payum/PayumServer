<?php
declare(strict_types=1);

namespace App\Api\Controller;

use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use App\Api\View\TokenToJsonConverter;
use App\Controller\ForwardExtensionTrait;
use App\InvalidJsonException;
use App\JsonDecode;
use App\Model\Payment;
use App\Schema\TokenSchemaBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TokenController
 * @package App\Api\Controller
 */
class TokenController
{
    use ForwardExtensionTrait;

    /**
     * @var RegistryInterface
     */
    protected $payum;

    /**
     * @var TokenToJsonConverter
     */
    private $tokenToJsonConverter;

    /**
     * @var TokenSchemaBuilder
     */
    private $schemaBuilder;

    /**
     * @var JsonDecode
     */
    private $jsonDecode;

    /**
     * @param Payum $payum
     * @param TokenToJsonConverter $tokenToJsonConverter
     * @param TokenSchemaBuilder $tokenSchemaBuilder
     * @param JsonDecode $jsonDecode
     */
    public function __construct(
        Payum $payum,
        TokenToJsonConverter $tokenToJsonConverter,
        TokenSchemaBuilder $tokenSchemaBuilder,
        JsonDecode $jsonDecode
    ) {
        $this->payum = $payum;
        $this->tokenToJsonConverter = $tokenToJsonConverter;
        $this->schemaBuilder = $tokenSchemaBuilder;
        $this->jsonDecode = $jsonDecode;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request) : JsonResponse
    {
        $this->forward400Unless('json' === $request->getContentType() || 'form' === $request->getContentType());

        try {
            $content = $request->getContent();
            $data = $this->jsonDecode->decode($content, $this->schemaBuilder->buildNew());
        } catch (InvalidJsonException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], 400);
        }

        /** @var Payment $payment */
        if (false == $payment = $this->payum->getStorage(Payment::class)->find($data['paymentId'])) {
            return new JsonResponse(['errors' => [
                'paymentId' => [
                    sprintf('Payment with id %s could not be found', $data['paymentId']),
                ],
            ]], 400);
        }

        if ($data['type'] === 'capture') {
            $token = $this->payum->getTokenFactory()->createCaptureToken('', $payment, $data['afterUrl'], [
                'payum_token' => null,
                'paymentId' => $payment->getId(),
            ]);

            return new JsonResponse(['token' => $this->tokenToJsonConverter->convert($token)], 201);
        } elseif ($data['type'] === 'authorize') {
            $token = $this->payum->getTokenFactory()->createAuthorizeToken('', $payment, $data['afterUrl'], [
                'payum_token' => null,
                'paymentId' => $payment->getId(),
            ]);

            return new JsonResponse(['token' => $this->tokenToJsonConverter->convert($token)], 201);
        }

        $this->forward400(sprintf('The token type %s is not supported', $data['type']));
    }
}
