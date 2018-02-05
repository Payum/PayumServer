<?php
declare(strict_types=1);

namespace App\Model;

use function Makasim\Values\get_value;
use function Makasim\Values\set_value;
use Payum\Core\Storage\IdentityInterface;

class Identity implements IdentityInterface
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * {@inheritDoc}
     */
    public function getClass() : string
    {
        return get_value($this, 'model_class');
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return get_value($this, 'model_id');
    }

    /**
     * {@inheritDoc}
     */
    public function serialize() : string
    {
        return serialize($this->values);
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized) : void
    {
        $this->values = unserialize($serialized);
    }

    public function __toString() : string
    {
        return $this->getClass() . '#' . $this->getId();
    }

    public static function createNew($class, $id) : self
    {
        $identity = new static();

        set_value($identity, 'model_class', $class);
        set_value($identity, 'model_id', $id);

        return $identity;
    }
}
