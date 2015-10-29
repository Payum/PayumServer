<?php
namespace Payum\Server\Model;

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
        return $this->getValue('identity', 'id') ?
            new Identity($this->getValue('identity', 'id'), $this->getValue('identity', 'class')) :
            null
        ;
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

        $this->setValue('identity', 'id', $details->getId());
        $this->setValue('identity', 'class', $details->getClass());
    }

    /**
     * {@inheritdoc}
     */
    public function getHash()
    {
        return $this->getSelfValue('hash');
    }

    /**
     * {@inheritdoc}
     */
    public function setHash($hash)
    {
        $this->setSelfValue('hash', $hash);
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetUrl()
    {
        return $this->getSelfValue('targetUrl');
    }

    /**
     * {@inheritdoc}
     */
    public function setTargetUrl($targetUrl)
    {
        $this->setSelfValue('targetUrl', $targetUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function getAfterUrl()
    {
        return $this->getSelfValue('afterUrl');
    }

    /**
     * {@inheritdoc}
     */
    public function setAfterUrl($afterUrl)
    {
        $this->setSelfValue('afterUrl', $afterUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function getGatewayName()
    {
        return $this->getSelfValue('gatewayName');
    }

    /**
     * {@inheritdoc}
     */
    public function setGatewayName($gatewayName)
    {
        $this->setSelfValue('gatewayName', $gatewayName);
    }
}