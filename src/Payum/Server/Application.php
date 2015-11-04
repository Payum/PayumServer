<?php
namespace Payum\Server;

use JDesrosiers\Silex\Provider\CorsServiceProvider;
use Payum\Server\Api\ApiControllerProvider;
use Payum\Server\Api\ApiProvider;
use Payum\Silex\PayumProvider;
use Silex\Application as SilexApplication;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

class Application extends SilexApplication
{
    public function __construct()
    {
        parent::__construct();


        $this['payum.root_dir'] = realpath(__DIR__.'/../..');

        $this->register(new CorsServiceProvider());
        $this->register(new RavenProvider());
        $this->register(new TwigServiceProvider());
        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new FormServiceProvider);
        $this->register(new ValidatorServiceProvider());
        $this->register(new TranslationServiceProvider());
        $this->register(new ServiceControllerServiceProvider);
        $payumProvider = new PayumProvider();
        $this->register($payumProvider);
        $this->mount('/payment', $payumProvider);

        $this->register(new ServiceProvider);
        $this->register(new ApiProvider());
        $this->register(new ApiControllerProvider());

        $app["cors.allowMethods"] = 'GET, OPTIONS, PUT, POST, DELETE';
    }
}