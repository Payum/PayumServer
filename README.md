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
$ curl -X POST -H "Content-Type: application/json" http://192.168.80.80:8000/api/config -d  '{"paymentName": "germany_paypal", "paymentFactory": "paypal", "username": "EDIT IT", "password": "EDIT IT", "signature": "EDIT IT", "sandbox": true}'
```

_**Note**: You must provide correct Paypal credentials._

The payment will be stored to `payum.yml` file in server root directory. You can later edit this file from console. Also you can get the list of payments

```bash
$ curl -X GET -H "Content-Type: application/json" http://192.168.80.80:8000/api/config'
```

## Purchase
 
First of all you have to create an order on the server. After, you have to redirect a payer to capture url:

```bash
$ curl -X POST -H "Content-Type: application/json" http://192.168.80.80:8000/api/order -d  '{"paymentName": "germany_paypal", "totalAmount": 123, "currenctCode": "USD"}' | python -m json.tool 
    % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                   Dload  Upload   Total   Spent    Left  Speed
  100   857    0   789  100    68  10587    912 --:--:-- --:--:-- --:--:-- 10662
  {
      "_links": {
          "authorize": "http://192.168.80.80:8000/authorize/urd3IGRnMsIiNNMiwqdKzOQFIAbIa-uR3XNAQ2573QA",
          "capture": "http://192.168.80.80:8000/capture/gT5OofuBMQp_D4lxfSuM4ZNx9yjgYdXoK96yiTsKHOI",
          "get": "http://192.168.80.80:8000/api/order/FiZzVbBu5ob2l2x4bvMCKezFU6QyuZRZ7WHlo6PzRU4",
          "notify": "http://192.168.80.80:8000/notify/VTc1D9U3Ab2AKBUp-kh9ycLf-Bbt608bxHyihYLuJGY"
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

## Tips

* Use urls provided with order to capture it (redirect user), get order details and so on.

* Exceptions tracking

    The server comes with built in support of [sentry](https://getsentry.com/welcome/) service. You just need to set a `SENTRY_DSN` environment (In Case you use apache add this `SetEnv SENTRY_DSN aDsn` to your vhost.):

    ```bash
    $ SENTRY_DSN=aDsn php -S 127.0.0.1:8000 web/index.php
    ```
* Try it [online](http://server.payum.forma-dev.com/)

## License

Code MIT [licensed](LICENSE.md).
