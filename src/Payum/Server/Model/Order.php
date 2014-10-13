<?php
namespace Payum\Server\Model;

use Payum\Core\Model\Order as BaseOrder;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Security\TokenInterface;

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

    /**
     * @var string[]
     */
    protected $links;

    /**
     * @var string[]
     */
    protected $tokens;

    public function __construct()
    {
        parent::__construct();

        $this->links = array();
        $this->tokens = array();

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
     * @return \string[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @return \string[]
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @param string $name
     * @param TokenInterface $token
     */
    public function addToken($name, TokenInterface $token)
    {
        $this->tokens[$name] = $token->getHash();
        $this->links[$name] = $token->getTargetUrl();
    }
}
