<?php
namespace Payum\Server\Api\Controller;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use Payum\Server\Api\View\FormToJsonConverter;
use Payum\Server\Api\View\TokenToJsonConverter;
use Payum\Server\Controller\ForwardExtensionTrait;
use Payum\Server\Model\Payment;
use Symfony\Component\Form\FormFactoryInterface;
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
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var TokenToJsonConverter
     */
    private $tokenToJsonConverter;

    /**
     * @var FormToJsonConverter
     */
    private $formToJsonConverter;

    /**
     * @param Payum $payum
     * @param FormFactoryInterface $formFactory
     * @param TokenToJsonConverter $tokenToJsonConverter
     * @param FormToJsonConverter $formToJsonConverter
     */
    public function __construct(
        Payum $payum,
        FormFactoryInterface $formFactory,
        TokenToJsonConverter $tokenToJsonConverter,
        FormToJsonConverter $formToJsonConverter
    ) {
        $this->payum = $payum;
        $this->formFactory = $formFactory;
        $this->tokenToJsonConverter = $tokenToJsonConverter;
        $this->formToJsonConverter = $formToJsonConverter;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction($content, Request $request)
    {
        $this->forward400Unless('json' == $request->getContentType() || 'form' == $request->getContentType());

        $rawToken = ArrayObject::ensureArrayObject($content);

        $form = $this->formFactory->create('create_token');
        $form->submit((array) $rawToken);
        if (false == $form->isValid()) {
            return new JsonResponse($this->formToJsonConverter->convertInvalid($form), 400);
        }

        $data = $form->getData();

        /** @var Payment $payment */
        $this->forward400Unless($payment = $this->payum->getStorage(Payment::class)->find($data['paymentId']));

        if ($data['type'] == 'capture') {
            $token = $this->payum->getTokenFactory()->createCaptureToken('', $payment, $data['afterUrl'], [
                'payum_token' => null,
                'paymentId' => $payment->getId(),
            ]);
        } else {
            $this->forward400(sprintf('The token type %s is not supported', $data['type']));
        }

        return new JsonResponse(['token' => $this->tokenToJsonConverter->convert($token)], 201);
    }
}
