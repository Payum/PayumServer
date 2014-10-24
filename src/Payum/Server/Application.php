<?php
namespace Payum\Server;

use JDesrosiers\Silex\Provider\CorsServiceProvider;
use Silex\Application as SilexApplication;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Payum\Server\ControllerProvider as PayumControllerProvider;
use Payum\Server\ServiceProvider as PayumServiceProvider;
use Payum\Server\ApiProvider as PayumApiProvider;

class Application extends SilexApplication
{
    public function __construct()
    {
        parent::__construct();

        $this['app.root_dir'] = realpath(__DIR__.'/../../../');

        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new CorsServiceProvider());
        $this->register(new ValidatorServiceProvider());
        $this->register(new FormServiceProvider);
        $this->register(new ServiceControllerServiceProvider);
        $this->register(new PayumServiceProvider);
        $this->register(new PayumControllerProvider);
        $this->register(new PayumApiProvider());
    }
}