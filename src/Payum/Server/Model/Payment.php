<?php
namespace Payum\Server\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;
use Payum\Core\Model\Payment as BasePayment;

/**
 * @Mongo\Document
 */
class Payment extends BasePayment
{
    /**
     * @Mongo\Id
     *
     * @var string $id
     */
    protected $id;

    /**
     * @Mongo\String
     *
     * @var string
     */
    protected $publicId;

    /**
     * @Mongo\String
     *
     * @var string
     */
    protected $gatewayName;

    /**
     * @Mongo\Hash
     *
     * @var string[]
     */
    protected $links;

    public function __construct()
    {
        parent::__construct();

        $this->links = [];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getGatewayName()
    {
        return $this->gatewayName;
    }

    /**
     * @param string $gatewayName
     */
    public function setGatewayName($gatewayName)
    {
        $this->gatewayName = $gatewayName;
    }

    /**
     * @return \string[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param string$name
     *
     * @return \string[]
     */
    public function getLink($name)
    {
        return $this->links[$name];
    }

    /**
     * @return \string[]
     */
    public function addLink($name, $link)
    {
        return $this->links[$name] = $link;
    }

    /**
     * @return mixed
     */
    public function getPublicId()
    {
        return $this->publicId;
    }

    /**
     * @param mixed $publicId
     */
    public function setPublicId($publicId)
    {
        $this->publicId = $publicId;
    }
}
