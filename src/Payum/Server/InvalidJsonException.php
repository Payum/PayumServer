<?php
namespace Payum\Server;

class InvalidJsonException extends \LogicException
{
    /**
     * @var array
     */
    private $errors;

    /**
     * @param array $errors
     */
    public function __construct(array $errors)
    {
        parent::__construct('Given json is not valid.');

        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}