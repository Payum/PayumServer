<?php
declare(strict_types=1);

namespace App\Schema;

use Payum\Core\Payum;
use App\Util\StringUtil;

/**
 * Class GatewaySchemaBuilder
 * @package App\Schema
 */
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
    public function buildDefault() : object
    {
        return (object) [
            '$schema' => 'http://json-schema.org/schema#',
            'type' => 'object',
            'properties' => (object) [
                'gatewayName' => (object) [
                    'type' => 'string',
                    'title' => StringUtil::nameToTitle('gatewayName'),
                    'pattern' => '^[\w\d\s_.-]*$',
                ],
                'factoryName' => (object) [
                    'type' => 'string',
                    'enum' => array_keys($this->payum->getGatewayFactories()),
                    'title' => StringUtil::nameToTitle('factoryName'),
                ],
            ],
            "required" => ["gatewayName", "factoryName"],
        ];
    }

    /**
     * @param $name
     *
     * @return object
     */
    public function build($name) : object
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
                } elseif (is_bool($value)) {
                    $properties[$name] = (object) ['type' => 'boolean', 'title' => $title];
                } else {
                    $properties[$name] = (object) ['type' => ['string', 'number', 'boolean', 'title' => $title]];
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
