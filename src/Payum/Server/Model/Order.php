<?php
namespace Payum\Server\Model;

use Payum\Core\Model\Order as BaseOrder;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Security\TokenInterface;
use Symfony\Component\Validator\Constraints\DateTime;

class Order extends BaseOrder
{
    /**
     * @var string
     */
    protected $paymentName;

    /**
     * @var string
     */
    protected $afterUrl;

    /**
     * @var array
     */
    protected $payments;

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
        $this->payments = array();
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

    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param array $payments
     */
    public function setPayments(array $payments)
    {
        $this->payments = $payments;
    }

    public function setDetails($details)
    {
        parent::setDetails($details);

        $this->payments[] = array(
            'status' => GetHumanStatus::STATUS_UNKNOWN,
            'date' => date(\DateTime::ISO8601),
            'name' => $this->paymentName,
            'details' => $this->details
        );
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
