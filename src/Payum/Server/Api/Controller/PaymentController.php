<?php
namespace Payum\Server\Api\Controller;

use function Makasim\Yadm\set_object_values;
use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Security\Util\Random;
use Payum\Core\Storage\StorageInterface;
use Payum\ISO4217\ISO4217;
use Payum\Server\Api\View\PaymentToJsonConverter;
use Payum\Server\Controller\ForwardExtensionTrait;
use Payum\Server\InvalidJsonException;
use Payum\Server\JsonDecode;
use Payum\Server\Model\Payment;
use Payum\Server\Schema\PaymentSchemaBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController
{
    use ForwardExtensionTrait;

    /**
     * @var RegistryInterface
     */
    protected $payum;

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
     * @param Payum $payum
     * @param PaymentToJsonConverter $paymentToJsonConverter
     * @param UrlGeneratorInterface $urlGenerator
     * @param PaymentSchemaBuilder $paymentSchemaBuilder
     * @param JsonDecode $jsonDecode
     */
    public function __construct(
        Payum $payum,
        PaymentToJsonConverter $paymentToJsonConverter,
        UrlGeneratorInterface $urlGenerator,
        PaymentSchemaBuilder $paymentSchemaBuilder,
        JsonDecode $jsonDecode
    ) {
        $this->payum = $payum;
        $this->paymentToJsonConverter = $paymentToJsonConverter;
        $this->urlGenerator = $urlGenerator;
        $this->schemaBuilder = $paymentSchemaBuilder;
        $this->jsonDecode = $jsonDecode;
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

        $payment = new Payment();
        set_object_values($payment, $data);

        $payment->setId(Random::generateToken());
        $payment->setNumber($payment->getNumber() ?: date('Ymd-'.mt_rand(10000, 99999)));

        $storage = $this->payum->getStorage($payment);
        $storage->update($payment);

        $selfUrl = $this->urlGenerator->generate('payment_get', ['id' => $payment->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse(
            ['payment' => $this->paymentToJsonConverter->convert($payment)],
            201,
            ['Location' => $selfUrl]
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        $this->forward404Unless($payment = $this->findRequestedPayment($request));

        $storage = $this->payum->getStorage($payment);
        $storage->delete($payment);

        //TODO remove related tokens.

        return new Response('', 204);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAction(Request $request)
    {
        $this->forward404Unless($payment = $this->findRequestedPayment($request));

        return new JsonResponse(array(
            'payment' => $this->paymentToJsonConverter->convert($payment),
        ));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function allAction(Request $request)
    {
        /** @var StorageInterface $storage */
        $storage = $this->payum->getStorage(Payment::class);

        $jsonPayments = [];
        foreach ($storage->findBy([]) as $payment) {
            $jsonPayments[] = $this->paymentToJsonConverter->convert($payment);

        }

        return new JsonResponse(array(
            'payments' => $jsonPayments,
        ));
    }

    /**
     * @param Request $request
     *
     * @return Payment
     */
    protected function findRequestedPayment(Request $request)
    {
        // TODO: add validation that id is not empty and model actually exists.
        $storage = $this->payum->getStorage(Payment::class);

        return $storage->find($request->attributes->get('id'));
    }
}
