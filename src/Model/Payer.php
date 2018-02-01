<?php
declare(strict_types=1);

namespace App\Model;

use Makasim\Values\ValuesTrait;

class Payer
{
    use ValuesTrait {
        setValue as public;
        getValue as public;
    }

    public function getId() : ?string
    {
        return $this->getValue('id');
    }

    public function setId(string $id) : void
    {
        $this->setValue('id', $id);
    }

    public function getEmail() : ?string
    {
        return $this->getValue('email');
    }

    public function setEmail(string $email) : void
    {
        $this->setValue('email', $email);
    }

    public function setFirstName(string $firstName) : void
    {
        $this->setValue('firstName', $firstName);
    }

    public function getFirstName() : string
    {
        return $this->getValue('firstName');
    }
}
