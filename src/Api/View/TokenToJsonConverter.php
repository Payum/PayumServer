<?php
declare(strict_types=1);

namespace App\Api\View;

use Payum\Core\Security\TokenInterface;

/**
 * Class TokenToJsonConverter
 * @package App\Api\View
 */
class TokenToJsonConverter
{
    /**
     * @param TokenInterface $token
     *
     * @return array
     */
    public function convert(TokenInterface $token)
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
