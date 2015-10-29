<?php
namespace Payum\Server;

use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter as BaseConverter;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Reply\HttpResponse;
use Pimple;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReplyToSymfonyResponseConverter extends BaseConverter
{
    /**
     * @var Pimple
     */
    protected $pimple;

    /**
     * @param Pimple $pimple
     */
    public function __construct(Pimple $pimple)
    {
        $this->pimple = $pimple;
    }

    /**
     * {@inheritDoc}
     */
    public function convert(ReplyInterface $reply)
    {
        if (isset($this->pimple['request']) && $this->pimple['request']->isXmlHttpRequest()) {
            if ($reply instanceof HttpRedirect) {
                return new JsonResponse([
                    'status' => $reply->getStatusCode(),
                    'body' => $reply->getUrl(),
                ]);
            } else if ($reply instanceof HttpResponse) {
                return new JsonResponse([
                    'status' => $reply->getStatusCode(),
                    'body' => $reply->getContent(),
                ]);
            }
        }

        return parent::convert($reply);
    }
}
