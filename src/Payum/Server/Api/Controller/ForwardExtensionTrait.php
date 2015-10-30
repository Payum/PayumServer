<?php
/**
 * Created by PhpStorm.
 * User: makasim
 * Date: 10/30/15
 * Time: 9:50 PM
 */

namespace Payum\Server\Api\Controller;


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