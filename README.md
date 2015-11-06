# PayumServer.
[![Build Status](https://travis-ci.org/Payum/PayumServer.png?branch=master)](https://travis-ci.org/Payum/PayumServer)
[![Total Downloads](https://poser.pugx.org/payum/payum-server/d/total.png)](https://packagist.org/packages/payum/payum-server)
[![Latest Stable Version](https://poser.pugx.org/payum/payum-server/version.png)](https://packagist.org/packages/payum/payum-server)

PHP 5.5+ Payment processing server. Setup once and rule them all.

## Setup & Run

```bash
$ php composer.phar create-project payum/payum-server --stability=dev
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/js-url/2.0.2/url.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        <script src="payum.js"></script>
    </head>

    <body style="margin: 20px;">
        <div id="payum-previous-payment"></div>

        <button id="pay-btn" class="btn" value="Create">Pay 1$</button>

        <div id="payum-container"></div>

        <script>
            var payumServerUrl = "http://"+window.location.hostname;
            var payum = new Payum(payumServerUrl);

            if (paymentId = url('?paymentId', window.location.href)) {
                payum.payment.get(paymentId, function(payment) {
                    $('#payum-previous-payment').text('Previous payment '+paymentId+' status: '+ payment.status);
                });
            }

            $('#pay-btn').click(function() {
                payum.payment.create(100, 'USD', function(payment) {
                    var afterUrl = "http://"+window.location.hostname +'/demo.html';

                    payum.token.create('capture', payment.id, afterUrl, function(token) {
                        payum.execute(token.targetUrl, '#payum-container');
                    });
                });
            });
        </script>
    </body>
</html>

```

_**Note**: We advice you to move payment and token creation code to the server side._

## Try it:

* Server: [http://server.payum.org/](http://server.payum.org/)
* Web client: [http://server.payum.org/client/index.html](http://server.payum.org/client/index.html#/app/settings?api=http:%2F%2Fserver.payum.org)
* Demo: [http://server.payum.org/demo.html](http://server.payum.org/demo.html)

## Donate

<a href='https://pledgie.com/campaigns/30526'><img alt='Click here to lend your support to: Your private payment processing server. Setup it once and rule them all and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/30526.png?skin_name=chrome' border='0' ></a>

## License

Code MIT [licensed](LICENSE.md).