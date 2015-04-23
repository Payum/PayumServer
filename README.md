# PayumServer.
[![Build Status](https://travis-ci.org/Payum/PayumServer.png?branch=master)](https://travis-ci.org/Payum/PayumServer)
[![Total Downloads](https://poser.pugx.org/payum/payum-server/d/total.png)](https://packagist.org/packages/payum/payum-server)
[![Latest Stable Version](https://poser.pugx.org/payum/payum-server/version.png)](https://packagist.org/packages/payum/payum-server)

Payment server. Setup your personal payment server and rule them all.

## Installation

```bash
$ php composer.phar create-project payum/payum-server --stability=dev
```

## Run

```bash
$ php -S 127.0.0.1:8000 web/index.php
```

## Configure gateway

Here we use a paypal as an example, but you can configure any other supported payments similar way.

```bash
$ curl -i -X POST -H "Content-Type: application/json" 127.0.0.1:8000/gateways -d  '{"gatewayName": "paypal", "factoryName": "paypal_express_checkout", "config": {"username": "foo", "password": "bar", "signature": "baz", "sandbox": true}}'
```

_**Note**: You must provide correct Paypal credentials._

## Create payment
 
You are ready to create a payment and purchase just do:

```bash
$ curl -i -X POST -H "Content-Type: application/json" 127.0.0.1:8000/payments -d  '{"gatewayName": "paypal", "totalAmount": 123, "currenctCode": "USD"}'
```

## Purchase

Redirect user to capture url you get with payment response, It should be something like this:

```bash
http://127.0.0.1:8000/payment/capture/gT5OofuBMQp_D4lxfSuM4ZNx9yjgYdXoK96yiTsKHOI
```

## GUI

There is a [client](https://github.com/Payum/PayumServerUI).    

## License

Code MIT [licensed](LICENSE.md).