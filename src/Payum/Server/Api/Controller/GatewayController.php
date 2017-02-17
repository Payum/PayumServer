<?php
namespace Payum\Server\Api\Controller;

use function Makasim\Yadm\set_object_values;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Api\View\FormToJsonConverter;
use Payum\Server\Api\View\GatewayConfigToJsonConverter;
use Payum\Server\Controller\ForwardExtensionTrait;
use Payum\Server\InvalidJsonException;
use Payum\Server\JsonDecode;
use Payum\Server\Model\GatewayConfig;
use Payum\Server\Schema\SchemaBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GatewayController
{
    use ForwardExtensionTrait;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var StorageInterface
     */
    private $gatewayConfigStorage;

    /**
     * @var GatewayConfigToJsonConverter
     */
    private $gatewayConfigToJsonConverter;
    /**
     * @var SchemaBuilder
     */
    private $schemaBuilder;
    /**
     * @var JsonDecode
     */
    private $jsonDecode;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     * @param StorageInterface $gatewayConfigStorage
     * @param GatewayConfigToJsonConverter $gatewayConfigToJsonConverter
     * @param SchemaBuilder $schemaBuilder
     * @param JsonDecode $jsonDecode
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        StorageInterface $gatewayConfigStorage,
        GatewayConfigToJsonConverter $gatewayConfigToJsonConverter,
        SchemaBuilder $schemaBuilder,
        JsonDecode $jsonDecode
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->gatewayConfigStorage = $gatewayConfigStorage;
        $this->gatewayConfigToJsonConverter = $gatewayConfigToJsonConverter;
        $this->schemaBuilder = $schemaBuilder;
        $this->jsonDecode = $jsonDecode;
    }

    public function createAction(Request $request)
    {
        $this->forward400Unless('json' == $request->getContentType());

        try {
            $content = $request->getContent();
            $data = $this->jsonDecode->decode($content, $this->schemaBuilder->buildDefault());
            $data = $this->jsonDecode->decode($content, $this->schemaBuilder->build($data['factoryName']));
        } catch (InvalidJsonException $e) {
            return new JsonResponse(['errors' => $e->getErrors(),], 400);
        }

        $gatewayConfig = new GatewayConfig();
        set_object_values($gatewayConfig, $data);

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

    public function allAction()
    {
        $convertedGatewayConfigs = array();
        foreach ($this->gatewayConfigStorage->findBy([]) as $gatewayConfig) {
            /** @var GatewayConfigInterface $gatewayConfig */

            $convertedGatewayConfigs[$gatewayConfig->getGatewayName()] = $this->gatewayConfigToJsonConverter->convert($gatewayConfig);
        }

        return new JsonResponse(array('gateways' => $convertedGatewayConfigs));
    }

    public function getAction($name)
    {
        $gatewayConfig = $this->findGatewayConfigByName($name);

        return new JsonResponse([
            'gateway' => $this->gatewayConfigToJsonConverter->convert($gatewayConfig)
        ]);
    }

    /**
     * @param string $name
     *
     * @return Response
     */
    public function deleteAction($name)
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

        /** @var GatewayConfigInterface[] $gatewayConfigs */
        $gatewayConfigs = $this->gatewayConfigStorage->findBy([
            'gatewayName' => $name
        ]);

        if (empty($gatewayConfigs)) {
            throw new NotFoundHttpException(sprintf('Config with name %s was not found.', $name));
        }

        return array_shift($gatewayConfigs);
    }
}
