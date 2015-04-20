<?php
namespace Payum\Server\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;
use Payum\Core\Model\GatewayConfig as BaseGatewayConfig;

/**
 * @Mongo\Document
 */
class GatewayConfig extends BaseGatewayConfig
{
    /**
     * @Mongo\Id
     *
     * @var string $id
     */
    protected $id;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
