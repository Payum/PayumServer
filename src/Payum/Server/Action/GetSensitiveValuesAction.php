<?php
namespace Payum\Server\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Security\SensitiveValue;
use Payum\Server\Request\GetSensitiveValuesRequest;
use Symfony\Component\HttpFoundation\Request;

class GetSensitiveValuesAction implements ActionInterface
{
    /**
     * @var Request
     */
    protected $httpRequest;

    /**
     * @param Request $httpRequest
     */
    public function __construct(Request $httpRequest)
    {
        $this->httpRequest = $httpRequest;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        if ($sensitiveValues = $this->httpRequest->query->get('sensitive')) {
            $sensitiveValues = json_decode(base64_decode($sensitiveValues), true);

            foreach ($sensitiveValues as &$value) {
                $value = new SensitiveValue($value);
            }

            $request->setValues($sensitiveValues);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof GetSensitiveValuesRequest;
    }
} 