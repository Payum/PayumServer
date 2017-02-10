# PayumServer.
[![Join the chat at https://gitter.im/Payum/Payum](https://badges.gitter.im/Payum/Payum.svg)](https://gitter.im/Payum/Payum?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Build Status](https://travis-ci.org/Payum/PayumServer.png?branch=master)](https://travis-ci.org/Payum/PayumServer)
[![Total Downloads](https://poser.pugx.org/payum/payum-server/d/total.png)](https://packagist.org/packages/payum/payum-server)
[![Latest Stable Version](https://poser.pugx.org/payum/payum-server/version.png)](https://packagist.org/packages/payum/payum-server)

PHP 5.6+ Payment processing server. Setup once and rule them all. [Here](https://medium.com/@maksim_ka2/your-personal-payment-processing-server-abcc8ed76804#.23mlps63n) you can find a good introduction to what it does and what problems it solves.

## Try it online:

* Demo: https://server.payum.forma-pro.com/demo.html
* Backend: [https://server-ui.payum.forma-pro.com](https://server-ui.payum.forma-pro.com/client/index.html#/app/settings?api=https%3A%2F%2Fserver.payum.forma-pro.com)
* Server: https://server.payum.forma-pro.com

## Run local server

Create docker-compose.yml file:

```yaml
version: '2'
services:
  payum-server:
    image: payum/payum-server
    environment:
      - PAYUM_MONGO_URI=mongodb://mongo:27017/payum_server
      - PAYUM_DEBUG=1
    links:
      - mongo
    ports:
      - "8080:80"

  mongo:
    image: mongo
```

and run `docker-compose up`. You server will be at `localhost:8080` port.

## Docker registry

The [payum/server](https://hub.docker.com/r/payum/server/) image and [payum/server-ui](https://hub.docker.com/r/payum/server-ui/) are built automatically on success push to the master branch.  

## Setup & Run

```bash
$ php composer.phar create-project payum/payum-server --stability=dev
$ cd payum-server
$ php -S 127.0.0.1:8000 web/app.php
```

An example on javascript:

```javascript
  // do new payment
  var payum = new Payum('http://localhost:8000');
    
  var payment = {totalAmount: 100, currencyCode: 'USD'};

  payum.payment.create(payment, function(payment) {
    var token = {
        type: 'capture',
        paymentId: payment.id,
        afterUrl: 'http://afterPaymentIsDoneUrl'
    };

    payum.token.create(token, function(token) {
      // do redirect to token.targetUrl or process at the same page like this:
      payum.execute(token.targetUrl, '#payum-container');
    });
  });
```

_**Note**: You might need a [web client](https://github.com/Payum/PayumServerUI) to manage payments gateways or you can use REST API._

## Support me

There are lots of features were done and even more stuff in todo list. If the library made your life easier cosider support me. You can eiether hire me to do some payment related stuff or donate. You can also send a mail with a feedback. [Tell me](https://github.com/makasim) about your experience with Payum. 

<a href='https://pledgie.com/campaigns/30526'><img alt='Click here to lend your support to: Your private payment processing server. Setup it once and rule them all and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/30526.png?skin_name=chrome' border='0' ></a>

## License

Code MIT [licensed](LICENSE.md).
