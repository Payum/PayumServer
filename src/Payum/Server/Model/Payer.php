<?php
namespace Payum\Server\Model;

use Makasim\Values\ValuesTrait;

class Payer
{
    use ValuesTrait {
        setValue as public;
        getValue as public;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getSelfValue('id');
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->setSelfValue('id', $id);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->getSelfValue('email');
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->setSelfValue('email', $email);
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->setSelfValue('firstName', $firstName);
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->getSelfValue('firstName');
    }
}