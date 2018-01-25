<?php
declare(strict_types=1);

namespace App\Tests\Functional\Api\Controller;

use Makasim\Yadm\Storage;
use App\Model\Payment;
use App\Test\ClientTestCase;
use App\Test\ResponseHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentControllerTest extends ClientTestCase
{
    use ResponseHelper;

    /**
     * @test
     */
    public function shouldAllowGetPayment()
    {
        /** @var Storage $storage */
        $storage = $this->getContainer()->get('payum.payment_storage');

        $payment = new Payment();
        $payment->setId(uniqid());
        $payment->setClientEmail('theExpectedPayment');
        $storage->insert($payment);

        $this->getClient()->request('GET', '/payments/' . $payment->getId());

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('payment', $content);

        $this->assertObjectHasAttribute('clientEmail', $content->payment);
        $this->assertEquals('theExpectedPayment', $content->payment->clientEmail);
    }

    /**
     * @test
     */
    public function shouldAllowDeletePayment()
    {
        $payment = new Payment();
        $payment->setId(uniqid());
        $payment->setClientEmail('theExpectedPayment');

        /** @var Storage $storage */
        $storage = $this->getContainer()->get('payum.payment_storage');
        $storage->insert($payment);

        //guard
        $this->getClient()->request('GET', '/payments/' . $payment->getId());
        $this->assertClientResponseStatus(200);

        $this->getClient()->request('DELETE', '/payments/' . $payment->getId());
        $this->assertClientResponseStatus(204);

        // @todo roll back expectException
//        $this->expectException(NotFoundHttpException::class);
        $this->getClient()->request('GET', '/payments/' . $payment->getId());
        $this->assertClientResponseStatus(404);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePayment()
    {
        $this->getClient()->postJson('/payments/', [
            'totalAmountInput' => 1.23,
            'currencyCode' => 'USD',
            'clientEmail' => 'foo@example.com',
            'clientId' => 'theClientId',
        ]);

        $this->assertClientResponseStatus(201);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('payment', $content);

        $this->assertObjectHasAttribute('clientEmail', $content->payment);
        $this->assertEquals('foo@example.com', $content->payment->clientEmail);

        $this->assertStringStartsWith(getenv('PAYUM_HTTP_HOST') . '/payments/', $this->getClient()->getResponse()->headers->get('Location'));
    }
}
