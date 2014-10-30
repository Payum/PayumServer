<?php
namespace Payum\Server;

use JDesrosiers\Silex\Provider\CorsServiceProvider;
use Payum\Server\Provider\RavenProvider;
use Payum\Silex\PayumProvider;
use Silex\Application as SilexApplication;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Payum\Server\Provider\ControllerProvider;
use Payum\Server\Provider\ServiceProvider;
use Payum\Server\Provider\ApiProvider;

class Application extends SilexApplication
{
    public function __construct()
    {
        parent::__construct();

        $this['payum.root_dir'] = realpath(__DIR__.'/../../../');
        $app["cors.allowMethods"] = 'GET, OPTIONS, PUT, POST, DELETE';

        $this->register(new RavenProvider());
        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new CorsServiceProvider());
        $this->register(new ValidatorServiceProvider());
        $this->register(new FormServiceProvider);
        $this->register(new ServiceControllerServiceProvider);
        $this->register(new PayumProvider());
        $this->register(new ServiceProvider);
        $this->register(new ControllerProvider);
        $this->register(new ApiProvider());
    }
}