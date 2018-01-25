<?php
namespace App\Model;

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
        return $this->getValue('id');
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->setValue('id', $id);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->getValue('email');
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->setValue('email', $email);
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->setValue('firstName', $firstName);
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->getValue('firstName');
    }
}