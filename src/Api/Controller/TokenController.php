<?php
declare(strict_types=1);

namespace App\Api\Controller;

use App\Model\PaymentToken;
use App\Storage\PaymentStorage;
use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use App\Api\View\TokenToJsonConverter;
use App\Controller\ForwardExtensionTrait;
use App\InvalidJsonException;
use App\JsonDecode;
use App\Model\Payment;
use App\Schema\TokenSchemaBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        Payum $payum,
        ContainerInterface $container,
        TokenToJsonConverter $tokenToJsonConverter,
        TokenSchemaBuilder $tokenSchemaBuilder,
        JsonDecode $jsonDecode
    ) {
        $this->payum = $payum;
        $this->tokenToJsonConverter = $tokenToJsonConverter;
        $this->schemaBuilder = $tokenSchemaBuilder;
        $this->jsonDecode = $jsonDecode;
        $this->container = $container;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Webmozart\Json\ValidationFailedException
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
        $payment = $this->container->get(PaymentStorage::class)->findById($data['paymentId']);

        if (!$payment) {
            return new JsonResponse(['errors' => [
                'paymentId' => [
                    sprintf('Payment with id %s could not be found', $data['paymentId']),
                ],
            ]], 400);
        }

        if ($data['type'] === 'capture') {
            /** @var PaymentToken $token */
            $token = $this->payum->getTokenFactory()->createCaptureToken('', $payment, $data['afterUrl'], [
                'payum_token' => null,
                'paymentId' => $payment->getId(),
            ]);

            return new JsonResponse(['token' => $this->tokenToJsonConverter->convert($token)], 201);
        }

        if ($data['type'] === 'authorize') {
            /** @var PaymentToken $token */
            $token = $this->payum->getTokenFactory()->createAuthorizeToken('', $payment, $data['afterUrl'], [
                'payum_token' => null,
                'paymentId' => $payment->getId(),
            ]);

            return new JsonResponse(['token' => $this->tokenToJsonConverter->convert($token)], 201);
        }

        $this->forward400(sprintf('The token type %s is not supported', $data['type']));
    }
}
