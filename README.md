# PayumServer.
[![Build Status](https://travis-ci.org/Payum/PayumServer.png?branch=master)](https://travis-ci.org/Payum/PayumServer)
[![Total Downloads](https://poser.pugx.org/payum/payum-server/d/total.png)](https://packagist.org/packages/payum/payum-server)
[![Latest Stable Version](https://poser.pugx.org/payum/payum-server/version.png)](https://packagist.org/packages/payum/payum-server)

PHP 5.3+ Payment micro service. Setup once and rule them all: Paypal, Stripe, Payex, Authorize.NET, Be2bill, Klarna, IPNs...

## Installation

```bash
$ php composer.phar create-project payum/payum-server . --stability=dev
```

## Run

```bash
$ php -S 127.0.0.1:8000 web/index.php
```

_**Note**: Never use built in web server on production. Set apache or nginx server._

## Configure

```bash
$ curl -i -X POST -H "Content-Type: application/json" http://server.payum.forma-dev.com/api/configs/payments -d  '{"name": "barpaypal", "factory": "paypal", "options": {"username": "foo", "password": "bar", "signature": "baz", "sandbox": true}}'
```

_**Note**: You must provide correct Paypal credentials._

## Create Order
 
First of all you have to create an order on the server. After, you have to redirect a payer to capture url:

```bash
$ curl -i -X POST -H "Content-Type: application/json" http://server.payum.forma-dev.com/api/orders -d  '{"paymentName": "barpaypal", "totalAmount": 123, "currenctCode": "USD"}'
```

As a response you have to get:
```json
{
    "order": {
        "clientEmail": null,
        "clientId": null,
        "currencyCode": null,
        "totalAmount": 123
        "details": [],
        "number": "20141013-81843",
        "payments": [
            {
                "status": "new",
                "date": "2014-10-21T21:58:54+0200",
                "name": "barpaypal",
                "details": []
            },
            {
                "status": "new",
                "date": "2014-10-21T21:59:06+0200",
                "name": "barpaypal",
                "details": {
                    "INVNUM": "20141021-36803",
                    "PAYMENTREQUEST_0_CURRENCYCODE": "USD",
                    "PAYMENTREQUEST_0_AMT": 1.23
                }
            },
            {
                "status": "new",
                "date": "2014-10-21T21:59:08+0200",
                "name": "barpaypal",
                "details": {
                    "INVNUM": "20141021-36803",
                    "PAYMENTREQUEST_0_CURRENCYCODE": "USD",
                    "PAYMENTREQUEST_0_AMT": 1.23,
                    "PAYMENTREQUEST_0_PAYMENTACTION": "Sale",
                    "RETURNURL": "http:\/\/192.168.80.80:8000\/capture\/1JlWkpdA0s4nCHqSAE3tHrHtlx94LiCuj5G27qcYhQU",
                    "CANCELURL": "http:\/\/192.168.80.80:8000\/capture\/1JlWkpdA0s4nCHqSAE3tHrHtlx94LiCuj5G27qcYhQU",
                    "TOKEN": "EC-4BH34851L07194223",
                    "TIMESTAMP": "2014-10-21T19:59:08Z",
                    "CORRELATIONID": "aaee2dd617056",
                    "ACK": "Success",
                    "VERSION": "65.1",
                    "BUILD": "13443904"
                }
            }
        ]
    },
    "_links": {
        "authorize": "http://server.payum.forma-dev.com/authorize/urd3IGRnMsIiNNMiwqdKzOQFIAbIa-uR3XNAQ2573QA",
        "capture": "http://server.payum.forma-dev.com/capture/gT5OofuBMQp_D4lxfSuM4ZNx9yjgYdXoK96yiTsKHOI",
        "get": "http://server.payum.forma-dev.com/api/orders/FiZzVbBu5ob2l2x4bvMCKezFU6QyuZRZ7WHlo6PzRU4",
        "notify": "http://server.payum.forma-dev.com/notify/VTc1D9U3Ab2AKBUp-kh9ycLf-Bbt608bxHyihYLuJGY"
    },
    "_tokens": {
        "authorize": "urd3IGRnMsIiNNMiwqdKzOQFIAbIa-uR3XNAQ2573QA",
        "capture": "gT5OofuBMQp_D4lxfSuM4ZNx9yjgYdXoK96yiTsKHOI",
        "get": "FiZzVbBu5ob2l2x4bvMCKezFU6QyuZRZ7WHlo6PzRU4",
        "notify": "VTc1D9U3Ab2AKBUp-kh9ycLf-Bbt608bxHyihYLuJGY"
    }
}
```

## Purchase

Redirect user to capture url you get with order response, It should be something like this:

```
http://server.payum.forma-dev.com/capture/gT5OofuBMQp_D4lxfSuM4ZNx9yjgYdXoK96yiTsKHOI
```

## Tips

* Find out which payment you can use:

    ```bash
    $ curl -i -X GET -H "Content-Type: application/json" http://server.payum.forma-dev.com/api/configs/payments'
    ```
    
* Find out which payments you can configure:

    ```bash
    $ curl -i -X GET -H "Content-Type: application/json" http://server.payum.forma-dev.com/api/configs/payments/meta'
    ```

* Find out which storage you can use:

    ```bash
    $ curl -i -X GET -H "Content-Type: application/json" http://server.payum.forma-dev.com/api/configs/storages'
    ```
    
* Find out which storages you can configure:

    ```bash
    $ curl -i -X GET -H "Content-Type: application/json" http://server.payum.forma-dev.com/api/configs/storages/meta'
    ```

* Try it [online](http://server.payum.forma-dev.com/)

* Enabled debug mode to get pretty printed json:

    ```bash
    $ PAYUM_SERVER_DEBUG=1 php -S 127.0.0.1:8000 web/index.php
    ```

* Exceptions tracking

    The server comes with built in support of [sentry](https://getsentry.com/welcome/) service. You just need to set a `SENTRY_DSN` environment (In Case you use apache add this `SetEnv SENTRY_DSN aDsn` to your vhost.):

    ```bash
    $ SENTRY_DSN=aDsn php -S 127.0.0.1:8000 web/index.php
    ```

## License

Code MIT [licensed](LICENSE.md).
