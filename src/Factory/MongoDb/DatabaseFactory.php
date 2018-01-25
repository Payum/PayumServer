<?php
declare(strict_types=1);

namespace App\Factory\MongoDb;

use LogicException;
use MongoDB\Client;
use MongoDB\Database;

/**
 * Class DatabaseFactory
 * @package App\Factory\MongoDb
 */
class DatabaseFactory
{
    /**
     * @param Client $client
     * @param string $uri
     *
     * @return Database
     */
    public function create(Client $client, string $uri) : Database
    {
        return $client->selectDatabase($this->getDatabase($uri));
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    private function getDatabase(string $uri) : string
    {
        if (false == $database = trim(parse_url($uri, PHP_URL_PATH), '/')) {
            throw new LogicException('The mongodb.uri must have database specified. For example http://localhost:27017/payum_server');
        }

        return $database;
    }
}
