# Payum Server

Run your payment server.

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

_**Note**: Never use built in web server on production. Set apache or nginx server._

## Use

First create a paypal payment:

```bash
$ curl \
   -X POST \
   -H "Content-Type: application/json" \
   http://dev.payum-server.com/api/payment \
   -d  '{ "PAYMENTREQUEST_0_CURRENCYCODE": "USD", "PAYMENTREQUEST_0_AMT": 10, "meta": { "name": "paypal", "purchase_after_url": "http://google.com" } }';


{
    "PAYMENTREQUEST_0_CURRENCYCODE": "USD",
    "PAYMENTREQUEST_0_AMT": 10,
    "meta": {
        "name": "paypal",
        "purchase_after_url": "http:\/\/google.com",
        "links": {
            "purchase": "http:\/\/dev.payum-server.com\/purchase\/jVMf78-uodhGS7YyFk9u4Qv6oftuVm_9T3Bmh1c2NNk?sensitive=W10%3D",
            "get": "http:\/\/dev.payum-server.com\/api\/payment\/P0T-8J50KwJCNWk6vquJaxtBtgwemA4kwXeaNBGMm4Q"
        }
    }
}
```

_**Note**: Do not store purchase url. Use it immediately._

or a stripe one:

```bash
$ curl \
   -X POST \
   -H "Content-Type: application/json" \
   http://dev.payum-server.com/api/payment \
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
            "purchase": "http:\/\/dev.payum-server.com\/purchase\/oXV3RJeJmJJDZMQbGQPmMyLYs9eUjtOGDdyLIdR2FT0?sensitive=eyJjYXJkIjp7Im51bWJlciI6IjU1NTU1NTY3NzgyNTAwMDAiLCJjdnYiOjEyMywiZXhwaXJ5TW9udGgiOjYsImV4cGlyeVllYXIiOjE2LCJmaXJzdE5hbWUiOiJmb28iLCJsYXN0TmFtZSI6ImJhciJ9fQ%3D%3D",
            "get": "http:\/\/dev.payum-server.com\/api\/payment\/WOFJgK-VrsxXsZu8sMHP0NsSridaWz-aiLO99XJxVlk"
        }
    }
}
```

_**Note**: Do not store purchase url. Use it immediately._

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
