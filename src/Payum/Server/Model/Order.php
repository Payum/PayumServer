<?php
namespace Payum\Server\Model;

use Payum\Core\Model\DetailsAggregateInterface;
use Payum\Core\Model\DetailsAwareInterface;

class Order implements DetailsAggregateInterface, DetailsAwareInterface, \IteratorAggregate
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $amount;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var array
     */
    protected $meta;

    /**
     * @var PaymentDetails
     */
    protected $details;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param array $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param array $meta
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator(array(
            'amount' => $this->amount,
            'currency' => $this->currency,
            'meta' => $this->meta,
            'details' => $this->details ? iterator_to_array($this->details) : null
        ));
    }
}
