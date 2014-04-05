# Payum Server

The idea of the service is to **get rid** of routine work. Whenever a developer has a task to integrate a payment they have to do same things:

* Think of payment abstractions, controllers, configurations.
* Integrate a payment with your favorite framework.
* Ways to integrate payments with your bussness layer.
* Correct handling of instant payment notifactions or callbacks.
* Storing payment details.
* Secured data handling.
* Security issues.
* Status calculation.

The service would allow to solve most of the mentioned tasks. Now you can install your own server or play with [online](server.payum.forma-dev.com) one

## Installation

```bash
$ mkdir payum-server && cd payum-server
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar create-project payum/payum-server . --stability=dev
```

## Configure

```bash
$ cp payum.yml.dist payum.yml
& vim payum.yml
```

## Run

```
$ php -S 127.0.0.1:8000 web/index.php
```

_**Note**: Never use built in web server on production. Set apache or nginx server.

## Use

First create a paypal payment:

```bash
$ curl \
   -X POST \
   -H "Content-Type: application/json" \
   http://server.payum.forma-dev.com/api/payment \
   -d  '{ "PAYMENTREQUEST_0_CURRENCYCODE": "USD", "PAYMENTREQUEST_0_AMT": 10, "meta": { "name": "paypal", "purchase_after_url": "http://google.com" } }';


{
    "PAYMENTREQUEST_0_CURRENCYCODE": "USD",
    "PAYMENTREQUEST_0_AMT": 10,
    "meta": {
        "name": "paypal",
        "purchase_after_url": "http:\/\/google.com",
        "links": {
            "purchase": "http:\/\/server.payum.forma-dev.com\/purchase\/xTmxk99oBja6NYHpt4-pFsOjC9xo4nqmQILP9MJU8AQ?sensitive=W10%3D",
            "get": "http:\/\/server.payum.forma-dev.com\/api\/payment\/Z6vEaRNuNpPkpZFiNbQ-LyQyh24Bgp6pXqwvRg13vz0"
        }
    }
}
```

_**Note**: Do not store purchase url. Use it immediately.

or a stripe one:

```bash
$ curl \
   -X POST \
   -H "Content-Type: application/json" \
   http://server.payum.forma-dev.com/api/payment \
   -d  '{ "amount": 0.10, "currency": "USD", "card": { "number": "5555556778250000", "cvv": 123, "expiryMonth": 6, "expiryYear": 16, "firstName": "foo", "lastName": "bar" }, "meta": { "name": "stripe", "purchase_after_url": "http://google.com" } }'

{
    "amount": 0.1,
    "currency": "USD",
    "card": {

    },
    "meta": {
        "name": "stripe",
        "purchase_after_url": "http:\/\/google.com",
        "links": {
            "purchase": "http:\/\/server.payum.forma-dev.com\/purchase\/8Mny5xfWWJOUdS7XFgGX7xEFoVcTfDMlqm4Ud_5Jkzo?sensitive=eyJjYXJkIjp7Im51bWJlciI6IjU1NTU1NTY3NzgyNTAwMDAiLCJjdnYiOjEyMywiZXhwaXJ5TW9udGgiOjYsImV4cGlyeVllYXIiOjE2LCJmaXJzdE5hbWUiOiJmb28iLCJsYXN0TmFtZSI6ImJhciJ9fQ%3D%3D",
            "get": "http:\/\/server.payum.forma-dev.com\/api\/payment\/gntU9dFlz7oWj0hBdu6U_sAS9RJaI4a80-QA2Tp83OM"
        }
    }
}
```

_**Note**: Do not store purchase url. Use it immediately.

Redirect user to purchase. After users will be redirected back to purchase_after_url.

## Get details and status

```bash
$ curl -X GET http://dev.payum-server.com/api/payment/WOFJgK-VrsxXsZu8sMHP0NsSridaWz-aiLO99XJxVlk

{
    "PAYMENTREQUEST_0_CURRENCYCODE": "USD",
    "PAYMENTREQUEST_0_AMT": 10,
    "meta": {
        "name": "paypal",
        "purchase_after_url": "http:\/\/google.com",
        "links": {
            "purchase": null,
            "get": "http:\/\/dev.payum-server.com\/api\/payment\/WOFJgK-VrsxXsZu8sMHP0NsSridaWz-aiLO99XJxVlk"
        },
        "status": 2
    }
}
```

Enjoy!