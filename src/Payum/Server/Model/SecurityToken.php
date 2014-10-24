<?php
namespace Payum\Server\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;
use Payum\Core\Model\Token;

/**
 * @Mongo\Document
 */
class SecurityToken extends Token
{
}