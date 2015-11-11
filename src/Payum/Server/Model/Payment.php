<?php
namespace Payum\Server\Model;

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
    public function getStatus()
    {
        return $this->getSelfValue('status', GetHumanStatus::STATUS_NEW);
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->setSelfValue('status', $status);
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
        return $this->getSelfValue('details', [], 'array');
    }

    /**
     * {@inheritdoc}
     */
    public function setDetails($details)
    {
        $this->setSelfValue('details', (array) $details);
    }

    /**
     * {@inheritdoc}
     */
    public function getNumber()
    {
        return $this->getSelfValue('number');
    }

    /**
     * {@inheritdoc}
     */
    public function setNumber($number)
    {
        $this->setSelfValue('number', $number);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->getSelfValue('description');
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->setSelfValue('description', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function getClientEmail()
    {
        return $this->getSelfValue('clientEmail');
    }

    /**
     * {@inheritdoc}
     */
    public function setClientEmail($clientEmail)
    {
        $this->setSelfValue('clientEmail', $clientEmail);
    }

    /**
     * {@inheritdoc}
     */
    public function getClientId()
    {
        return $this->getSelfValue('clientId');
    }

    /**
     * {@inheritdoc}
     */
    public function setClientId($clientId)
    {
        $this->setSelfValue('clientId', $clientId);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalAmount()
    {
        return $this->getSelfValue('totalAmount', null, 'int');
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalAmount($totalAmount)
    {
        $this->setSelfValue('totalAmount', $totalAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyCode()
    {
        return $this->getSelfValue('currencyCode');
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->setSelfValue('currencyCode', $currencyCode);
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
        return $this->getSelfValue('gatewayName');
    }

    /**
     * @param string $gatewayName
     */
    public function setGatewayName($gatewayName)
    {
        $this->setSelfValue('gatewayName', $gatewayName);
    }

    /**
     * @return Payer
     */
    public function getPayer()
    {
        if (false == $this->getValue('self', 'payer')) {
            $this->setObject('self', 'payer', new Payer());
        }

        return $this->getObject('self', 'payer', Payer::class);
    }
}