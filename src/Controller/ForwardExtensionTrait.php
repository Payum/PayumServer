<?php
namespace App\Controller;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    public function forward404If($condition, $message = '')
    {
        if ($condition) {
            $this->forward404($message);
        }
    }

    public function forward404Unless($condition, $message = '')
    {
        $this->forward404If(!$condition, $message);
    }

    public function forward404($message = '')
    {
        throw new NotFoundHttpException($message);
    }
}