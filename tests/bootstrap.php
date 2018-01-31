<?php
declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

if (!$loader = @include __DIR__ . '/../vendor/autoload.php') {
    echo <<<EOM
You must set up the project dependencies by running the following commands:

    curl -s http://getcomposer.org/installer | php
    php composer.phar install

EOM;

    exit(1);
}

if (!isset($_SERVER['APP_ENV'])) {
    if (!class_exists(Dotenv::class)) {
        throw new \RuntimeException('APP_ENV environment variable is not defined. You need to define environment variables for configuration or add "symfony/dotenv" as a Composer dependency to load variables from a .env file.');
    }

    (new Dotenv())->load(__DIR__ . '/../.env', __DIR__ . '/../.test.env');
}

$loader->add('App\Tests', __DIR__);
$loader->add('App\Test', __DIR__);