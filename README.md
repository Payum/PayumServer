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

## Use

Send:

```bash
$ curl -X POST -H "Content-Type: application/json" http://dev.payum-server.com/api/payment -d  '{ "PAYMENTREQUEST_0_CURRENCYCODE": "USD", "PAYMENTREQUEST_0_AMT": 10, "meta": { "name": "paypal", "purchase_after_url": "http://google.com" } }'

{
    "PAYMENTREQUEST_0_CURRENCYCODE": "USD",
    "PAYMENTREQUEST_0_AMT": 10,
    "meta": {
        "name": "paypal",
        "purchase_after_url": "http:\/\/google.com",
        "links": {
            "purchase": "http:\/\/dev.payum-server.com\/purchase\/zMF-GbVkFhC2bigLDI0zdvbKxYyULimTlJ36CCiC7bE",
            "get": "http:\/\/dev.payum-server.com\/api\/payment\/aW0jPEVXDiUSf4VakQgUPfS6YcF1G2FO_8-cPOvvRbk"
        }
    }
}
```

Redirect user to purchase. After users will be redirected back to purchase_after_url.

## Get details and status

```bash
$ curl -X GET http://dev.payum-server.com/api/payment\aW0jPEVXDiUSf4VakQgUPfS6YcF1G2FO_8-cPOvvRbk

{
    "PAYMENTREQUEST_0_CURRENCYCODE": "USD",
    "PAYMENTREQUEST_0_AMT": 10,
    "meta": {
        "name": "paypal",
        "purchase_after_url": "http:\/\/google.com",
        "links": {
            "purchase": null,
            "get": "http:\/\/dev.payum-server.com\/api\/payment\/aW0jPEVXDiUSf4VakQgUPfS6YcF1G2FO_8-cPOvvRbk"
        },
        "status": 2
    }
}
```

Enjoy!