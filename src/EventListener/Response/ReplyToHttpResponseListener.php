<?php
declare(strict_types=1);

namespace App\EventListener\Response;

use Payum\Bundle\PayumBundle\EventListener;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Reply\ReplyInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ReplyToHttpResponseListener extends EventListener\ReplyToHttpResponseListener
{
    /**
     * @var ReplyToSymfonyResponseConverter
     */
    protected $replyToSymfonyResponseConverter;

    public function __construct(ReplyToSymfonyResponseConverter $replyToSymfonyResponseConverter)
    {
        $this->replyToSymfonyResponseConverter = $replyToSymfonyResponseConverter;
    }

    public function onKernelException(GetResponseForExceptionEvent $event) : void
    {
        if (false == $event->getException() instanceof ReplyInterface) {
            return;
        }

        $response = $this->replyToSymfonyResponseConverter->convert($event->getException());

        /**
         * Header 'X-Status-Code' is deprecated
         * @see https://github.com/symfony/symfony-docs/blob/master/reference/events.rst
         */
        $event->allowCustomResponseCode();
        $event->setResponse($response);
    }
}
