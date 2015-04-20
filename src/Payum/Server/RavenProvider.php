<?php
namespace Payum\Server;

use Silex\Application as SilexApplication;
use Silex\ServiceProviderInterface;

class RavenProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(SilexApplication $app)
    {
        if ($dsn = getenv('SENTRY_DSN')) {
            $app['raven.client'] = $client = new \Raven_Client($dsn);

            $errorHandler = new \Raven_ErrorHandler($client);
            $errorHandler->registerExceptionHandler();
            $errorHandler->registerErrorHandler();
            $errorHandler->registerShutdownFunction();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function boot(SilexApplication $app)
    {
    }
}
