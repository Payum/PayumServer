# Payum Server.

Look ma! How it is easy to setup the payment server. Setup the server and it will take care of your payments. Easy to setup and tweak.

## Installation

```bash
$ mkdir payum-server && cd payum-server
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar create-project payum/payum-server . --stability=dev
```

## Run

```bash
$ php -S 127.0.0.1:8000 web/index.php
```

_**Note**: Never use built in web server on production. Set apache or nginx server._

## Configure

```bash
$ curl -i -X POST -H "Content-Type: application/json" http://server.payum.forma-dev.com/api/payments/configs -d  '{"name": "barpaypal", "factory": "paypal", "options": {"username": "foo", "password": "bar", "signature": "baz", "sandbox": true}}'
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
      },
      "order": {
          "clientEmail": null,
          "clientId": null,
          "currencyCode": null,
          "details": [],
          "number": "20141013-81843",
          "paymentName": "paypal",
          "paymentStatus": "new",
          "totalAmount": 123
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
    $ curl -X GET -H "Content-Type: application/json" http://server.payum.forma-dev.com/api/payments/configs'
    ```
    
* Find out which payments you can configure:

    ```bash
    $ curl -X GET -H "Content-Type: application/json" http://server.payum.forma-dev.com/api/payments/factories'
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
