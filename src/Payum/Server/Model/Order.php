<?php
namespace Payum\Server\Model;

use Payum\Core\Model\Order as BaseOrder;
use Payum\Core\Request\GetHumanStatus;

class Order extends BaseOrder
{
    /**
     * @var string
     */
    protected $paymentName;

    /**
     * @var string
     */
    protected $paymentStatus;

    /**
     * @var string
     */
    protected $afterUrl;

    public function __construct()
    {
        parent::__construct();

        $this->paymentStatus = GetHumanStatus::STATUS_NEW;
    }

    /**
     * @return string
     */
    public function getPaymentName()
    {
        return $this->paymentName;
    }

    /**
     * @param string $paymentName
     */
    public function setPaymentName($paymentName)
    {
        $this->paymentName = $paymentName;
    }

    /**
     * @return string
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @param string $paymentStatus
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;
    }

    /**
     * @return string
     */
    public function getAfterUrl()
    {
        return $this->afterUrl;
    }

    /**
     * @param string $afterUrl
     */
    public function setAfterUrl($afterUrl)
    {
        $this->afterUrl = $afterUrl;
    }
}
