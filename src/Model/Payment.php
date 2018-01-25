<?php
declare(strict_types=1);

namespace App\Model;

use Makasim\Values\CastTrait;
use Makasim\Values\ObjectsTrait;
use Makasim\Values\ValuesTrait;
use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Request\GetHumanStatus;

/**
 * Class Payment
 * @package App\Model
 */
class Payment
{
    use ValuesTrait {
        setValue as public;
        getValue as public;
    }
    use ObjectsTrait;
    use CastTrait;

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
    public function getStatus()
    {
        return $this->getValue('status', GetHumanStatus::STATUS_NEW);
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->setValue('status', $status);
    }

    /**
     * @var CreditCardInterface
     */
    protected $creditCard;

    /**
     * {@inheritdoc}
     */
    public function getDetails()
    {
        return $this->getValue('details', [], 'array');
    }

    /**
     * {@inheritdoc}
     */
    public function setDetails($details)
    {
        $this->setValue('details', (array) $details);
    }

    /**
     * {@inheritdoc}
     */
    public function getNumber()
    {
        return $this->getValue('number');
    }

    /**
     * {@inheritdoc}
     */
    public function setNumber($number)
    {
        $this->setValue('number', $number);
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->getValue('createdAt', null, \DateTime::class);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->setValue('createdAt', $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->getValue('description');
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->setValue('description', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function getClientEmail()
    {
        return $this->getValue('clientEmail');
    }

    /**
     * {@inheritdoc}
     */
    public function setClientEmail($clientEmail)
    {
        $this->setValue('clientEmail', $clientEmail);
    }

    /**
     * {@inheritdoc}
     */
    public function getClientId()
    {
        return $this->getValue('clientId');
    }

    /**
     * {@inheritdoc}
     */
    public function setClientId($clientId)
    {
        $this->setValue('clientId', $clientId);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalAmount()
    {
        return $this->getValue('totalAmount', null, 'int');
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalAmount($totalAmount)
    {
        $this->setValue('totalAmount', $totalAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyCode()
    {
        return $this->getValue('currencyCode');
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->setValue('currencyCode', $currencyCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreditCard(CreditCardInterface $creditCard)
    {
        $this->creditCard = $creditCard;
    }

    /**
     * @return string
     */
    public function getGatewayName()
    {
        return $this->getValue('gatewayName');
    }

    /**
     * @param string $gatewayName
     */
    public function setGatewayName($gatewayName)
    {
        $this->setValue('gatewayName', $gatewayName);
    }

    /**
     * @param Payer $payer
     */
    public function setPayer(Payer $payer)
    {
        $this->setObject('payer', $payer);
    }

    /**
     * @return Payer | object
     */
    public function getPayer() : Payer
    {
        if (false == $this->getValue('payer')) {
            $this->setPayer(new Payer());
        }

        return $this->getObject('payer', Payer::class);
    }
}