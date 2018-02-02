<?php
declare(strict_types=1);

namespace App\Model;

use Makasim\Values\ValuesTrait;
use Payum\Core\Exception\LogicException;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Token;
use Payum\Core\Storage\IdentityInterface;

class PaymentToken extends Token
{
    use ValuesTrait;

    /**
     * {@inheritdoc}
     *
     * @return IdentityInterface | null
     */
    public function getDetails() : ?Identity
    {
        return new Identity(
            $this->getValue('paymentId'),
            $this->getValue('paymentClass', Payment::class)
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param IdentityInterface $details
     */
    public function setDetails($details) : void
    {
        if (false == $details instanceof IdentityInterface) {
            throw new LogicException('Only instance of identity supported as token details');
        }

        $this->setValue('paymentId', $details->getId());
        $this->setValue('paymentClass', $details->getClass());
    }

    public function getId() : string
    {
        return $this->getValue('id');
    }

    /**
     * {@inheritdoc}
     */
    public function getHash() : ?string
    {
        return $this->getValue('hash');
    }

    /**
     * {@inheritdoc}
     */
    public function setHash($hash) : void
    {
        $this->setValue('hash', $hash);
        $this->setValue('id', $hash);
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetUrl() : string
    {
        return $this->getValue('targetUrl');
    }

    /**
     * {@inheritdoc}
     */
    public function setTargetUrl($targetUrl) : void
    {
        $this->setValue('targetUrl', $targetUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function getAfterUrl() : string
    {
        return $this->getValue('afterUrl');
    }

    /**
     * {@inheritdoc}
     */
    public function setAfterUrl($afterUrl) : void
    {
        $this->setValue('afterUrl', $afterUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function getGatewayName() : ?string
    {
        return $this->getValue('gatewayName');
    }

    /**
     * {@inheritdoc}
     */
    public function setGatewayName($gatewayName) : void
    {
        $this->setValue('gatewayName', $gatewayName);
    }
}