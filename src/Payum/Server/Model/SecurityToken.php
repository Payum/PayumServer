<?php
namespace Payum\Server\Model;

use Makasim\Values\ValuesTrait;
use Makasim\Yadm\PersistableTrait;
use MongoDB\BSON\Persistable;
use Payum\Core\Exception\LogicException;
use Payum\Core\Model\Identity;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\IdentityInterface;

class SecurityToken implements TokenInterface, Persistable
{
    use ValuesTrait;
    use PersistableTrait;

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
        return $this->getSelfValue('id');
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
        $this->setSelfValue('id', $hash);
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