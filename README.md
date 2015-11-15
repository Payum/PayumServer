# PayumServer.
[![Build Status](https://travis-ci.org/Payum/PayumServer.png?branch=master)](https://travis-ci.org/Payum/PayumServer)
[![Total Downloads](https://poser.pugx.org/payum/payum-server/d/total.png)](https://packagist.org/packages/payum/payum-server)
[![Latest Stable Version](https://poser.pugx.org/payum/payum-server/version.png)](https://packagist.org/packages/payum/payum-server)

PHP 5.5+ Payment processing server. Setup once and rule them all.

## Try it:

* Server: [http://server.payum.org/](http://server.payum.org/)
* Web client: [http://server.payum.org/client/index.html](http://server.payum.org/client/index.html#/app/settings?api=http:%2F%2Fserver.payum.org)
* Demo: [http://server.payum.org/demo.html](http://server.payum.org/demo.html)

## Distribution 

Docker container on docker hub. to be done. 

## Setup & Run

```bash
$ php composer.phar create-project payum/payum-server --stability=dev
$ cd payum-server
$ php -S 127.0.0.1:8000 web/index.php
```

_**Note**: You might need a [web client](https://github.com/Payum/PayumServerUI) to manage payments gateways or you can use REST API._

## Support us

There are lots of features were done and even more stuff in todo list. If the library made your life easier cosider support me. You can eiether hire me to do some payment related stuff or donate. You can also send a mail with a feedback. [Tell me](https://github.com/makasim) about your expiriens with Payum. 

<a href='https://pledgie.com/campaigns/30526'><img alt='Click here to lend your support to: Your private payment processing server. Setup it once and rule them all and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/30526.png?skin_name=chrome' border='0' ></a>

## License

Code MIT [licensed](LICENSE.md).
