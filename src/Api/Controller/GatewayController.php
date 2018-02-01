<?php
declare(strict_types=1);

namespace App\Api\Controller;

use function Makasim\Values\set_values;
use Payum\Core\Model\GatewayConfigInterface;
use App\Storage\GatewayConfigStorage;
use App\Api\View\GatewayConfigToJsonConverter;
use App\Controller\ForwardExtensionTrait;
use App\InvalidJsonException;
use App\JsonDecode;
use App\Model\GatewayConfig;
use App\Schema\GatewaySchemaBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GatewayController
{
    use ForwardExtensionTrait;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var GatewayConfigStorage
     */
    private $gatewayConfigStorage;

    /**
     * @var GatewayConfigToJsonConverter
     */
    private $gatewayConfigToJsonConverter;

    /**
     * @var GatewaySchemaBuilder
     */
    private $schemaBuilder;

    /**
     * @var JsonDecode
     */
    private $jsonDecode;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        GatewayConfigStorage $gatewayConfigStorage,
        GatewayConfigToJsonConverter $gatewayConfigToJsonConverter,
        GatewaySchemaBuilder $schemaBuilder,
        JsonDecode $jsonDecode
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->gatewayConfigStorage = $gatewayConfigStorage;
        $this->gatewayConfigToJsonConverter = $gatewayConfigToJsonConverter;
        $this->schemaBuilder = $schemaBuilder;
        $this->jsonDecode = $jsonDecode;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Webmozart\Json\ValidationFailedException
     */
    public function createAction(Request $request) : JsonResponse
    {
        $this->forward400Unless('json' == $request->getContentType());

        try {
            $content = $request->getContent();
            $data = $this->jsonDecode->decode($content, $this->schemaBuilder->buildDefault());
            $data = $this->jsonDecode->decode($content, $this->schemaBuilder->build($data['factoryName']));
        } catch (InvalidJsonException $e) {
            return new JsonResponse(['errors' => $e->getErrors(),], 400);
        }

        if ($this->gatewayConfigStorage->findOne(['gatewayName' => $data['gatewayName']])) {
            return new JsonResponse([
                'errors' => [
                    'gatewayName' => [
                        sprintf('Gateway with such name "%s" already exists', $data['gatewayName']),
                    ],
                ],
            ], 400);
        }

        /** @var GatewayConfig $gatewayConfig */
        $gatewayConfig = $this->gatewayConfigStorage->create();
        set_values($gatewayConfig, $data);

        $this->gatewayConfigStorage->insert($gatewayConfig);

        $getUrl = $this->urlGenerator->generate('gateway_get',
            ['name' => $gatewayConfig->getGatewayName()],
            UrlGenerator::ABSOLUTE_URL
        );

        return new JsonResponse(
            [
                'gateway' => $this->gatewayConfigToJsonConverter->convert($gatewayConfig),
            ],
            201,
            [
                'Location' => $getUrl,
            ]
        );
    }

    public function allAction() : JsonResponse
    {
        $convertedGatewayConfigs = [];
        foreach ($this->gatewayConfigStorage->find([]) as $gatewayConfig) {
            /** @var GatewayConfigInterface $gatewayConfig */

            $convertedGatewayConfigs[$gatewayConfig->getGatewayName()] = $this->gatewayConfigToJsonConverter->convert($gatewayConfig);
        }

        return new JsonResponse(['gateways' => $convertedGatewayConfigs]);
    }

    public function getAction(string $name) : JsonResponse
    {
        $gatewayConfig = $this->findGatewayConfigByName($name);

        return new JsonResponse([
            'gateway' => $this->gatewayConfigToJsonConverter->convert($gatewayConfig),
        ]);
    }

    public function deleteAction(string $name) : Response
    {
        $gatewayConfig = $this->findGatewayConfigByName($name);

        $this->gatewayConfigStorage->delete($gatewayConfig);

        return new Response('', 204);
    }

    protected function findGatewayConfigByName(string $name) : GatewayConfigInterface
    {
        if (false == $name) {
            throw new NotFoundHttpException(sprintf('Config name is empty.', $name));
        }

        $gatewayConfig = $this->gatewayConfigStorage->findOne([
            'gatewayName' => $name,
        ]);

        if (empty($gatewayConfig)) {
            throw new NotFoundHttpException(sprintf('Config with name %s was not found.', $name));
        }

        return $gatewayConfig;
    }
}
