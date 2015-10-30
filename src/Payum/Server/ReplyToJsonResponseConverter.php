<?php
namespace Payum\Server;

use Payum\Core\Exception\LogicException;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Reply\HttpResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReplyToJsonResponseConverter
{
    /**
     * {@inheritDoc}
     */
    public function convert(ReplyInterface $reply)
    {
        if ($reply instanceof HttpResponse) {
            return new JsonResponse([
                'status' => $reply->getStatusCode(),
                'headers' => $reply->getHeaders(),
                'content' => $reply->getContent(),
            ], 200, ['X-Status-Code' => 200]);
        }

        $ro = new \ReflectionObject($reply);

        throw new LogicException(
            sprintf('Cannot convert reply %s to http response.', $ro->getShortName()),
            null,
            $reply
        );
    }
}
