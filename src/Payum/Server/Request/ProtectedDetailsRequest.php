<?php
namespace Payum\Server\Request;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Security\SensitiveValue;

class ProtectedDetailsRequest
{
    /**
     * @var array
     */
    protected $details;

    /**
     * @var SensitiveValue[]
     */
    protected $sensitiveDetails;

    /**
     * @param mixed $details
     */
    public function __construct($details)
    {
        $this->details = $details;
        $this->sensitiveDetails = array();
    }

    /**
     * @param string $name
     */
    public function protect($name)
    {
        $details = ArrayObject::ensureArrayObject($this->details);

        if ($details[$name]) {
            $this->sensitiveDetails[$name] = new SensitiveValue($details[$name]);
            $details[$name] = new SensitiveValue($details[$name]);
        }
    }

    /**
     * @return string
     */
    public function getSensitiveDetailsAsString()
    {
        $values = array();
        foreach ($this->sensitiveDetails as $name => $sensitiveDetail) {
            $values[$name] = $sensitiveDetail->peek();
        }

        return base64_encode(json_encode(array_filter($values)));
    }

    /**
     * @return SensitiveValue[]
     */
    public function getSensitiveDetails()
    {
        return $this->sensitiveDetails;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }
}
