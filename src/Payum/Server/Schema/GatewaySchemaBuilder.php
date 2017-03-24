<?php
namespace Payum\Server\Schema;

use Payum\Core\Payum;
use Payum\Server\Util\StringUtil;

class GatewaySchemaBuilder
{
    /**
     * @var Payum
     */
    private $payum;

    /**
     * @param Payum $payum
     */
    public function __construct(Payum $payum)
    {
        $this->payum = $payum;
    }

    /**
     * @return object
     */
    public function buildDefault()
    {
        return (object) [
            '$schema' => 'http://json-schema.org/schema#',
            'type' => 'object',
            'properties' => (object) [
                'gatewayName' => (object)  [
                    'type' => 'string',
                    'title' => StringUtil::nameToTitle('gatewayName'),
                    'pattern' => '^[\w\d\s_.-]*$'
                ],
                'factoryName' => (object)  [
                    'type' => 'string',
                    'enum' => array_keys($this->payum->getGatewayFactories()),
                    'title' => StringUtil::nameToTitle('factoryName'),
                ]
            ],
            "required" => [ "gatewayName", "factoryName" ]
        ];
    }

    /**
     * @param $name
     *
     * @return object
     */
    public function build($name)
    {
        $config = $this->payum->getGatewayFactory($name)->createConfig();

        $properties = [];
        if (isset($config['payum.default_options'])) {

            foreach ($config['payum.default_options'] as $name => $value) {
                $title = StringUtil::nameToTitle($name);

                if (is_string($value)) {
                    $properties[$name] = (object) [
                        'type' => 'string',
                        'maxLength' => '512',
                        'title' => $title,
                    ];
                } else if (is_bool($value)) {
                    $properties[$name] = (object) ['type' => 'boolean', 'title' => $title];
                } else {
                    $properties[$name] = (object)  [ 'type' => ['string', 'number', 'boolean', 'title' => $title]];
                }
            }
        }

        $required = [];
        if (isset($config['payum.required_options'])) {
            $required = $config['payum.required_options'];
        }

        $configSchema = [
            'type' => 'object',
            'properties' => (object) $properties,
            'required' => $required,
        ];

        $schema = $this->buildDefault();
        $schema->properties->config = (object) $configSchema;

        return $schema;
    }
}
