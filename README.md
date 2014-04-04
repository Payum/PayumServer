# Payum Server

Run your payment server.

## Installation

```bash
$ mkdir payum-server && cd payum-server
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar create-project payum/payum-server --stability=dev
```

## Run

```
$ php -S 127.0.0.1:8000 web/index.php
```

## Use

Send:

```
POST http://127.0.0.1:8000/paypal

{  "PAYMENTREQUEST_0_CURRENCYCODE": "USD", "PAYMENTREQUEST_0_AMT": 10 }
```

Use purchase from the response:

```
{
    "_links": {
        "purchase": "http:\/\/192.168.80.80:8000\/capture\/xo8fUca4uV6zCOd_7cG7o0YVa0atF87oUPEnSSDN51k",
        "get": "http:\/\/192.168.80.80:8000\/paypal\/uFyEo18bvSSv9tc19hQrEybUcwpacBTaoSeJCNzqMeo"
    }
}
```