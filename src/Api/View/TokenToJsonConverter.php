<?php
declare(strict_types=1);

namespace App\Api\View;

use Payum\Core\Security\TokenInterface;

class TokenToJsonConverter
{
    public function convert(TokenInterface $token) : array
    {
        return [
            'hash' => $token->getHash(),
            'afterUrl' => $token->getAfterUrl(),
            'targetUrl' => $token->getTargetUrl(),
            'paymentId' => $token->getDetails()->getId(),
        ];
    }
}
