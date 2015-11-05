<?php
namespace Payum\Server\Api\View;

use Payum\Server\Model\SecurityToken;

class TokenToJsonConverter
{
    /**
     * @param SecurityToken $token
     *
     * @return array
     */
    public function convert(SecurityToken $token)
    {
        $normalizedToken = [
            'hash' => $token->getHash(),
            'afterUrl' => $token->getAfterUrl(),
            'targetUrl' => $token->getTargetUrl(),
            'paymentId' => $token->getDetails()->getId(),
        ];

        return $normalizedToken;
    }
}
