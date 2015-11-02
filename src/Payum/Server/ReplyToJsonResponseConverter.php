<?php
namespace Payum\Server;

use Payum\Core\Exception\LogicException;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Bridge\Symfony\Reply\HttpResponse as SymfonyHttpResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReplyToJsonResponseConverter
{
    /**
     * {@inheritDoc}
     */
    public function convert(ReplyInterface $reply)
    {
        if ($reply instanceof SymfonyHttpResponse) {
            $response = $reply->getResponse();

            return new JsonResponse([
                'status' => $response->getStatusCode(),
                'headers' => array_replace(
                    $response->headers->all(),
                    ['content-type' => 'application/vnd.payum+json']
                ),
                'content' => $response->getContent(),
            ], $response->getStatusCode(), ['X-Status-Code' => 200]);
        }

        if ($reply instanceof HttpResponse) {
            return new JsonResponse([
                'status' => $reply->getStatusCode(),
                'headers' => array_replace(
                    $reply->getHeaders(),
                    ['content-type' => 'application/vnd.payum+json']
                ),
                'content' => $reply->getContent(),
            ], $reply->getStatusCode(), ['X-Status-Code' => $reply->getStatusCode()]);
        }

        $ro = new \ReflectionObject($reply);

        throw new LogicException(
            sprintf('Cannot convert reply %s to http response.', $ro->getShortName()),
            null,
            $reply
        );
    }
}
