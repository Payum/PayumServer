<?php
declare(strict_types=1);

namespace App;

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

    public function getErrors() : array
    {
        return $this->errors;
    }
}
