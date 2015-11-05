# PayumServer.
[![Build Status](https://travis-ci.org/Payum/PayumServer.png?branch=master)](https://travis-ci.org/Payum/PayumServer)
[![Total Downloads](https://poser.pugx.org/payum/payum-server/d/total.png)](https://packagist.org/packages/payum/payum-server)
[![Latest Stable Version](https://poser.pugx.org/payum/payum-server/version.png)](https://packagist.org/packages/payum/payum-server)

PHP 5.5+ Payment processing server. Setup once and rule them all.

## Installation

```bash
$ php composer.phar create-project payum/payum-server --stability=dev
```

## Run

```bash
$ cd payum-server
$ php -S 127.0.0.1:8000 web/index.php
```

_**Note**: You might need a [web client](https://github.com/Payum/PayumServerUI) to manage payments gateways or you can use REST API._

## Demo

This is just the smallest example:

```html
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">

        <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        <script src="http://server.payum.org/payum.js"></script>
    </head>

    <body style="margin: 20px;">
        <button id="pay-btn" class="btn" value="Create">Pay 1$</button>

        <div id="payum-container"></div>

        <script>
            var payum = new Payum("http://"+window.location.hostname);

            $('#pay-btn').click(function() {
                payum.payment.create(100, 'USD', function(payment) {
                    var afterUrl = 'http://dev.payum-server.com/client/index.html#/app/payments/details/'+payment.id;

                    payum.token.create('capture', payment.id, afterUrl, function(token) {
                        payum.execute(token.targetUrl, '#payum-container');
                    });
                });
            });
        </script>
    </body>
</html>
```

_**Note**: We advice you to me payment and token creation code to your server side._

Try it:

* Server: [http://server.payum.org/](http://server.payum.org/)
* Web client: [http://server.payum.org/client/index.html](http://server.payum.org/client/index.html#/app/settings?api=http:%2F%2Fserver.payum.org)
* Demo: [http://server.payum.org/demo.html](http://server.payum.org/demo.html)

## License

Code MIT [licensed](LICENSE.md).