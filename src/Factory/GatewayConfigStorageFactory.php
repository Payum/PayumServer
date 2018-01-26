<?php
declare(strict_types=1);

namespace App\Factory;

use App\Model\GatewayConfig;
use Makasim\Yadm\Storage;
use Payum\Core\Bridge\Defuse\Security\DefuseCypher;
use Payum\Core\Storage\CryptoStorageDecorator;
use Payum\Core\Storage\StorageInterface;
use App\Storage\YadmStorage;

/**
 * Class GatewayConfigStorageFactory
 * @package App\Factory
 */
class GatewayConfigStorageFactory
{
    /**
     * @param Storage $storage
     *
     * @return StorageInterface
     */
    public static function create(Storage $storage) : StorageInterface
    {
        $defuseSecret = getenv('DEFUSE_SECRET');

        if ($defuseSecret) {
            $payumCypher = new DefuseCypher($defuseSecret);
        }

        $gatewayConfigStorage = new YadmStorage($storage, YadmStorage::DEFAULT_ID_PROPERTY, GatewayConfig::class);

        if (isset($payumCypher)) {
            $gatewayConfigStorage = new CryptoStorageDecorator($gatewayConfigStorage, $payumCypher);
        }

        return $gatewayConfigStorage;
    }
}
