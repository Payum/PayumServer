<?php
namespace Payum\Server\Model;

use Makasim\Yadm\ValuesTrait;
use Payum\Core\Exception\LogicException;
use Payum\Core\Model\Identity;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\IdentityInterface;

class SecurityToken implements TokenInterface
{
    use ValuesTrait;

    /**
     * @var StorageRegistryInterface
     */
    protected static $storageRegistry;

    /**
     * {@inheritdoc}
     *
     * @return IdentityInterface|null
     */
    public function getDetails()
    {
        return $this->getValue('identity') ?
            new Identity($this->getValue('identity.id'), $this->getValue('identity.class')) :
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

        $this->setValue('identity.id', $details->getId());
        $this->setValue('identity.class', $details->getClass());
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        if ($identity = $this->getDetails()) {
            return static::$storageRegistry->getStorage($identity->getClass())->find($identity);
        }
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
        $payment = $this->getPayment();

        return $payment ? $payment->getGatewayName() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setGatewayName($gatewayName)
    {
        // the gateway name is taken from the underlying payment model.
    }

    /**
     * @param StorageRegistryInterface $storageRegistry
     */
    public static function injectStorageRegistry(StorageRegistryInterface $storageRegistry)
    {
        static::$storageRegistry = $storageRegistry;
    }
}