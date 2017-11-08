<?php
namespace Payum\Server\Api\Controller;

use Payum\Core\Security\Util\Random;
use Payum\ISO4217\ISO4217;
use Payum\Server\Api\View\PaymentToJsonConverter;
use Payum\Server\Controller\ForwardExtensionTrait;
use Payum\Server\InvalidJsonException;
use Payum\Server\JsonDecode;
use Payum\Server\Schema\PaymentSchemaBuilder;
use Payum\Server\Storage\PaymentStorage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController
{
    use ForwardExtensionTrait;

    /**
     * @var PaymentToJsonConverter
     */
    private $paymentToJsonConverter;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var PaymentSchemaBuilder
     */
    private $schemaBuilder;

    /**
     * @var JsonDecode
     */
    private $jsonDecode;

    /**
     * @var PaymentStorage
     */
    private $paymentStorage;

    /**
     * @param PaymentToJsonConverter $paymentToJsonConverter
     * @param UrlGeneratorInterface $urlGenerator
     * @param PaymentStorage $paymentStorage
     * @param PaymentSchemaBuilder $paymentSchemaBuilder
     * @param JsonDecode $jsonDecode
     */
    public function __construct(
        PaymentToJsonConverter $paymentToJsonConverter,
        UrlGeneratorInterface $urlGenerator,
        PaymentStorage $paymentStorage,
        PaymentSchemaBuilder $paymentSchemaBuilder,
        JsonDecode $jsonDecode
    ) {
        $this->paymentToJsonConverter = $paymentToJsonConverter;
        $this->urlGenerator = $urlGenerator;
        $this->schemaBuilder = $paymentSchemaBuilder;
        $this->jsonDecode = $jsonDecode;
        $this->paymentStorage = $paymentStorage;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $this->forward400Unless('json' == $request->getContentType() || 'form' == $request->getContentType());

        try {
            $content = $request->getContent();
            $data = $this->jsonDecode->decode($content, $this->schemaBuilder->buildNew());
        } catch (InvalidJsonException $e) {
            return new JsonResponse(['errors' => $e->getErrors(),], 400);
        }

        $currency = (new ISO4217)->findByAlpha3($data['currencyCode']);
        $data['totalAmount'] = (int) ($data['totalAmountInput'] * pow(10, $currency->getExp()));

        $payment = $this->paymentStorage->hydrate($data);
        $payment->setId(Random::generateToken());
        $payment->setNumber($payment->getNumber() ?: date('Ymd-'.mt_rand(10000, 99999)));
        $payment->setCreatedAt(new \DateTime('now'));

        $this->paymentStorage->insert($payment);

        $selfUrl = $this->urlGenerator->generate('payment_get', ['id' => $payment->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse(
            ['payment' => $this->paymentToJsonConverter->convert($payment)],
            201,
            ['Location' => $selfUrl]
        );
    }

    /**
     * @return Response
     */
    public function deleteAction($id)
    {
        $this->forward404Unless($payment = $this->paymentStorage->findOne(['id' => $id]));

        $this->paymentStorage->delete($payment);

        //TODO remove related tokens.

        return new Response('', 204);
    }

    /**
     * @return JsonResponse
     */
    public function getAction($id)
    {
        $this->forward404Unless($payment = $this->paymentStorage->findById($id));

        return new JsonResponse([
            'payment' => $this->paymentToJsonConverter->convert($payment),
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function allAction()
    {
        $jsonPayments = [];
        foreach ($this->paymentStorage->find([], ['limit' => 50]) as $payment) {
            $jsonPayments[] = $this->paymentToJsonConverter->convert($payment);

        }

        return new JsonResponse(['payments' => $jsonPayments]);
    }
}
