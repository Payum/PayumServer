<?php
namespace Payum\Server\Controller;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait ForwardExtensionTrait
{
    public function forward400If($condition, $message = '')
    {
        if ($condition) {
            $this->forward400($message);
        }
    }

    public function forward400Unless($condition, $message = '')
    {
        $this->forward400If(!$condition, $message);
    }

    public function forward400($message = '')
    {
        throw new BadRequestHttpException($message);
    }
}