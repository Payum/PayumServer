<?php
namespace App\Model;

use Makasim\Values\ValuesTrait;
use Payum\Core\Exception\LogicException;
use Payum\Core\Model\Identity;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\IdentityInterface;

class SecurityToken implements TokenInterface
{
    use ValuesTrait;

    /**
     * {@inheritdoc}
     *
     * @return IdentityInterface|null
     */
    public function getDetails()
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
    public function setDetails($details)
    {
        if (false == $details instanceof IdentityInterface) {
            throw new LogicException('Only instance of identity supported as token details');
        }

        $this->setValue('paymentId', $details->getId());
        $this->setValue('paymentClass', $details->getClass());
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getValue('id');
    }

    /**
     * {@inheritdoc}
     */
    public function getHash()
    {
        return $this->getValue('hash');
    }

    /**
     * {@inheritdoc}
     */
    public function setHash($hash)
    {
        $this->setValue('hash', $hash);
        $this->setValue('id', $hash);
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetUrl()
    {
        return $this->getValue('targetUrl');
    }

    /**
     * {@inheritdoc}
     */
    public function setTargetUrl($targetUrl)
    {
        $this->setValue('targetUrl', $targetUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function getAfterUrl()
    {
        return $this->getValue('afterUrl');
    }

    /**
     * {@inheritdoc}
     */
    public function setAfterUrl($afterUrl)
    {
        $this->setValue('afterUrl', $afterUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function getGatewayName()
    {
        return $this->getValue('gatewayName');
    }

    /**
     * {@inheritdoc}
     */
    public function setGatewayName($gatewayName)
    {
        $this->setValue('gatewayName', $gatewayName);
    }
}