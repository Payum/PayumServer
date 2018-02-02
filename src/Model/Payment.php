<?php
declare(strict_types=1);

namespace App\Model;

use DateTime;
use Makasim\Values\CastTrait;
use Makasim\Values\ObjectsTrait;
use Makasim\Values\ValuesTrait;
use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Request\GetHumanStatus;

class Payment
{
    use ValuesTrait {
        setValue as public;
        getValue as public;
    }
    use ObjectsTrait;
    use CastTrait;

    /**
     * @var CreditCardInterface
     */
    protected $creditCard;

    public function getId() : ?string
    {
        return $this->getValue('id');
    }

    public function setId(string $id) : void
    {
        $this->setValue('id', $id);
    }

    public function getStatus() : string
    {
        return $this->getValue('status', GetHumanStatus::STATUS_NEW);
    }

    public function setStatus($status) : void
    {
        $this->setValue('status', $status);
    }

    public function getDetails()
    {
        return $this->getValue('details', [], 'array');
    }

    public function setDetails($details) : void
    {
        $this->setValue('details', (array) $details);
    }

    public function getNumber()
    {
        return $this->getValue('number');
    }

    public function setNumber($number) : void
    {
        $this->setValue('number', $number);
    }

    public function getCreatedAt() : DateTime
    {
        return $this->getValue('createdAt', null, DateTime::class);
    }

    public function setCreatedAt(DateTime $createdAt) : void
    {
        $this->setValue('createdAt', $createdAt);
    }

    public function getDescription() : ?string
    {
        return $this->getValue('description');
    }

    public function setDescription(string $description) : void
    {
        $this->setValue('description', $description);
    }

    public function getClientEmail() : string
    {
        return $this->getValue('clientEmail');
    }

    public function setClientEmail(string $clientEmail) : void
    {
        $this->setValue('clientEmail', $clientEmail);
    }

    public function getClientId()
    {
        return $this->getValue('clientId');
    }

    public function setClientId($clientId) : void
    {
        $this->setValue('clientId', $clientId);
    }

    public function getTotalAmount()
    {
        return $this->getValue('totalAmount', null, 'int');
    }

    public function setTotalAmount($totalAmount) : void
    {
        $this->setValue('totalAmount', $totalAmount);
    }

    public function getCurrencyCode()
    {
        return $this->getValue('currencyCode');
    }

    public function setCurrencyCode($currencyCode) : void
    {
        $this->setValue('currencyCode', $currencyCode);
    }

    public function getCreditCard()
    {
        return $this->creditCard;
    }

    public function setCreditCard(CreditCardInterface $creditCard) : void
    {
        $this->creditCard = $creditCard;
    }

    public function getGatewayName() : ?string
    {
        return $this->getValue('gatewayName');
    }

    public function setGatewayName(?string $gatewayName) : void
    {
        $this->setValue('gatewayName', $gatewayName);
    }

    public function setPayer(Payer $payer) : void
    {
        $this->setObject('payer', $payer);
    }

    /**
     * @return Payer | \object
     */
    public function getPayer() : Payer
    {
        if (false == $this->getValue('payer')) {
            $this->setPayer(new Payer());
        }

        return $this->getObject('payer', Payer::class);
    }
}
