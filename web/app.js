document.addEventListener('DOMContentLoaded', () => {
    const payumServerUrl = location.protocol + '//' + window.location.hostname;
    const payum = new Payum(payumServerUrl);
    const paymentId = url('?paymentId', window.location.href);

    document.querySelector('#pay-btn').addEventListener('click', () => {
        const payment = {totalAmountInput: 1, currencyCode: 'USD'};

        payum.payment.create(payment, (payment) => {
            const token = {
                type: 'capture',
                paymentId: payment.id,
                afterUrl: window.location.href
            };

            payum.token.create(token, (responseToken) => {
                payum.execute(responseToken.targetUrl, '#payum-container');
            });
        });
    });

    document.querySelector('#pay-krona-btn').addEventListener('click', () => {
        const payment = {totalAmountInput: 1, currencyCode: 'SEK', gatewayName: 'Klarna Checkout'};

        payum.payment.create(payment, (payment) => {
            const token = {
                type: 'authorize',
                paymentId: payment.id,
                afterUrl: window.location.href
            };


            payum.token.create(token, (responseToken) => {
                payum.execute(responseToken.targetUrl, '#payum-container');
            });
        });
    });

    // show status of previous payment.
    if (paymentId) {
        payum.payment.get(paymentId, (payment) => {
            document.querySelector('#payum-previous-payment').insertAdjacentHTML('afterBegin', 'Previous payment ' + paymentId + ' status: ' + payment.status);
        });
    }
});