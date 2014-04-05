<?php
namespace Payum\Server\Request;

use Payum\Core\Security\SensitiveValue;

class GetSensitiveValuesRequest
{
    /**
     * @var SensitiveValue[]
     */
    protected $values = array();

    /**
     * @param SensitiveValue[] $values
     */
    public function setValues(array $values)
    {
        $this->values = $values;
    }

    /**
     * @return SensitiveValue[]
     */
    public function getValues()
    {
        return $this->values;
    }
}
