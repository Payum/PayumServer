<?php
namespace Payum\Server\Api\Controller;

use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Api\View\FormToJsonConverter;
use Payum\Server\Api\View\GatewayConfigToJsonConverter;
use Payum\Server\Model\GatewayConfig;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GatewayController
{
    use ForwardExtensionTrait;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var FormToJsonConverter
     */
    private $formToJsonConverter;

    /**
     * @var StorageInterface
     */
    private $gatewayConfigStorage;

    /**
     * @var GatewayConfigToJsonConverter
     */
    private $gatewayConfigToJsonConverter;

    /**
     * @param FormFactoryInterface $formFactory
     * @param UrlGeneratorInterface $urlGenerator
     * @param FormToJsonConverter $formToJsonConverter
     * @param StorageInterface $gatewayConfigStorage
     * @param GatewayConfigToJsonConverter $gatewayConfigToJsonConverter
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator,
        FormToJsonConverter $formToJsonConverter,
        StorageInterface $gatewayConfigStorage,
        GatewayConfigToJsonConverter $gatewayConfigToJsonConverter
    ) {
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->formToJsonConverter = $formToJsonConverter;
        $this->gatewayConfigStorage = $gatewayConfigStorage;
        $this->gatewayConfigToJsonConverter = $gatewayConfigToJsonConverter;
    }

    public function createAction($content, Request $request)
    {
        $this->forward400Unless('json' == $request->getContentType());

        $form = $this->formFactory->create('payum_gateway_config', null, [
            'data_class' => GatewayConfig::class,
            'csrf_protection' => false,
        ]);

        $form->submit($content);
        if ($form->isValid()) {
            /** @var GatewayConfigInterface $gatewayConfig */
            $gatewayConfig = $form->getData();

            $this->gatewayConfigStorage->update($gatewayConfig);

            $getUrl = $this->urlGenerator->generate('gateway_get',
                array('name' => $gatewayConfig->getGatewayName()),
                $absolute = true
            );

            return new JsonResponse(
                array(
                    'gateway' => $this->gatewayConfigToJsonConverter->convert($gatewayConfig),
                ),
                201,
                array(
                    'Location' => $getUrl
                )
            );
        }

        return new JsonResponse($this->formToJsonConverter->convertInvalid($form), 400);
    }

    public function allAction(Request $request)
    {
        $convertedGatewayConfigs = array();
        foreach ($this->gatewayConfigStorage->findBy([]) as $gatewayConfig) {
            /** @var GatewayConfigInterface $gatewayConfig */

            $convertedGatewayConfigs[$gatewayConfig->getGatewayName()] = $this->gatewayConfigToJsonConverter->convert($gatewayConfig);
        }

        return new JsonResponse(array('gateways' => $convertedGatewayConfigs));
    }

    public function getAction($name, Request $request)
    {
        $gatewayConfig = $this->findGatewayConfigByName($name);

        return new JsonResponse(array('gateway' => $this->gatewayConfigToJsonConverter->convert($gatewayConfig)));
    }

    /**
     * @param string $name
     *
     * @return Response
     */
    public function deleteAction($name, Request $request)
    {
        $gatewayConfig = $this->findGatewayConfigByName($name);

        $this->gatewayConfigStorage->delete($gatewayConfig);

        return new Response('', 204);
    }

    /**
     * @param string $name
     *
     * @return GatewayConfigInterface
     */
    protected function findGatewayConfigByName($name)
    {
        if (false == $name) {
            throw new NotFoundHttpException(sprintf('Config name is empty.', $name));
        }

        /** @var GatewayConfigInterface $gatewayConfigs */
        $gatewayConfigs = $this->gatewayConfigStorage->findBy([
            'self.gatewayName' => $name
        ]);

        if (empty($gatewayConfigs)) {
            throw new NotFoundHttpException(sprintf('Config with name %s was not found.', $name));
        }

        return array_shift($gatewayConfigs);
    }
}
