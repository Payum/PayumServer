<?php
namespace Payum\Server\Request;

class GetSensitiveKeysRequest
{
    /**
     * @var string[]
     */
    protected $keys = array();

    /**
     * @param string[] $keys
     */
    public function setKeys(array $keys)
    {
        $this->keys = $keys;
    }

    /**
     * @return string[]
     */
    public function getKeys()
    {
        return $this->keys;
    }
}
