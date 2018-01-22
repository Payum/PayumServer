<?php
declare(strict_types=1);

namespace Payum\Server\Factory;

use Makasim\Yadm\Storage;
use Payum\Core\Bridge\Defuse\Security\DefuseCypher;
use Payum\Core\Storage\CryptoStorageDecorator;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Storage\YadmStorage;

/**
 * Class GatewayConfigStorageFactory
 * @package Payum\Server\Factory
 */
class GatewayConfigStorageFactory
{
    /**
     * @param Storage $storage
     *
     * @return StorageInterface
     */
    public function create(Storage $storage) : StorageInterface
    {
        $defuseSecret = getenv('DEFUSE_SECRET');

        if ($defuseSecret) {
            $payumCypher = new DefuseCypher($defuseSecret);
        }

        $gatewayConfigStorage = new YadmStorage($storage);

        if (isset($payumCypher)) {
            $gatewayConfigStorage = new CryptoStorageDecorator($gatewayConfigStorage, $payumCypher);
        }

        return $gatewayConfigStorage;
    }
}
