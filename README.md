# PayumServer.
[![Join the chat at https://gitter.im/Payum/Payum](https://badges.gitter.im/Payum/Payum.svg)](https://gitter.im/Payum/Payum?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Build Status](https://travis-ci.org/Payum/PayumServer.png?branch=master)](https://travis-ci.org/Payum/PayumServer)
[![Total Downloads](https://poser.pugx.org/payum/payum-server/d/total.png)](https://packagist.org/packages/payum/payum-server)
[![Latest Stable Version](https://poser.pugx.org/payum/payum-server/version.png)](https://packagist.org/packages/payum/payum-server)

PHP 7.2+ Payment processing server. Setup once and rule them all. [Here](https://medium.com/@maksim_ka2/your-personal-payment-processing-server-abcc8ed76804#.23mlps63n) you can find a good introduction to what it does and what problems it solves.

## Try it online:

* Demo: https://server.payum.forma-pro.com/demo.html
* Backend: [https://server-ui.payum.forma-pro.com](https://server-ui.payum.forma-pro.com/#!/app/settings?api=https:%2F%2Fserver.payum.forma-pro.com)
* Server: https://server.payum.forma-pro.com

## Run local server for development
1. You need to have installed `docker` and `docker-compose`
2. Copy .env file `cp .env.dist .env`
3. Run build container and up `docker-compose down && docker-compose build && docker-compose up`
4. Go inside php-fpm container `docker-compose exec php-fpm /bin/ash`
5. Run composer `composer install`
6. Check result at [configured host](http://payum-server-symfony.local:8080)

## Test local server
1. Setup `TEST_PAYUM_MONGO_URI` in `phpunit.xml.dist`
2. Setup `PAYUM_HTTP_HOST` in `phpunit.xml.dist`
3. Setup `PAYUM_SERVER_NAME` in `phpunit.xml.dist`
3. Setup `PAYUM_NGINX_PORT` in `phpunit.xml.dist`

## Docker registry

The [payum/server](https://hub.docker.com/r/payum/server/) image and [payum/server-ui](https://hub.docker.com/r/payum/server-ui/) are built automatically on success push to the master branch.  

## Setup & Run

```bash
$ php composer.phar create-project payum/payum-server --stability=dev
$ cd payum-server
$ php -S 127.0.0.1:8000 web/app.php
```

An example on javascript:

```javascript
  // do new payment
  var payum = new Payum('http://localhost:8000');
    
  var payment = {totalAmount: 100, currencyCode: 'USD'};

  payum.payment.create(payment, function(payment) {
    var token = {
        type: 'capture',
        paymentId: payment.id,
        afterUrl: 'http://afterPaymentIsDoneUrl'
    };

    payum.token.create(token, function(token) {
      // do redirect to token.targetUrl or process at the same page like this:
      payum.execute(token.targetUrl, '#payum-container');
    });
  });
```

_**Note**: You might need a [web client](https://github.com/Payum/PayumServerUI) to manage payments gateways or you can use REST API._

[Site](https://payum.forma-pro.com/)

## Developed by Forma-Pro

Forma-Pro is a full stack development company which interests also spread to open source development. 
Being a team of strong professionals we have an aim an ability to help community by developing cutting edge solutions in the areas of e-commerce, docker & microservice oriented architecture where we have accumulated a huge many-years experience. 
Our main specialization is Symfony framework based solution, but we are always looking to the technologies that allow us to do our job the best way. We are committed to creating solutions that revolutionize the way how things are developed in aspects of architecture & scalability.

If you have any questions and inquires about our open source development, this product particularly or any other matter feel free to contact at opensource@forma-pro.com
## License

Code MIT [licensed](LICENSE.md).
