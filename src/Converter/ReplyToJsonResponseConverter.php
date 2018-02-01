<?php
declare(strict_types=1);

namespace App\Converter;

use Payum\Core\Exception\LogicException;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Bridge\Symfony\Reply\HttpResponse as SymfonyHttpResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReplyToJsonResponseConverter
{
    /**
     * {@inheritDoc}
     */
    public function convert(ReplyInterface $reply) : JsonResponse
    {
        $headers = $statusCode = $content = null;

        if ($reply instanceof SymfonyHttpResponse) {
            $response = $reply->getResponse();

            $statusCode = $response->getStatusCode();
            $headers = $response->headers->all();
            $content = $response->getContent();
        } elseif ($reply instanceof HttpPostRedirect) {
            $statusCode = $reply->getStatusCode();
            $headers = $reply->getHeaders();
            $content = $this->preparePostRedirectContent($reply);
        } elseif ($reply instanceof HttpResponse) {
            $statusCode = $reply->getStatusCode();
            $headers = $reply->getHeaders();
            $content = $reply->getContent();
        } else {
            $ro = new \ReflectionObject($reply);

            throw new LogicException(
                sprintf('Cannot convert reply %s to http response.', $ro->getShortName()),
                null,
                $reply
            );
        }

        $fixedHeaders = [];
        foreach ($headers as $name => $value) {
            $fixedHeaders[str_replace('- ', '-', ucwords(str_replace('-', '- ', $name)))] = $value;
        }
        $fixedHeaders['Content-Type'] = 'application/vnd.payum+json';

        return new JsonResponse(
            [
                'status' => $statusCode,
                'headers' => $fixedHeaders,
                'content' => $content,
            ],
            $statusCode,
            [
                'X-Status-Code' => $statusCode,
            ]
        );
    }

    protected function preparePostRedirectContent(HttpPostRedirect $reply) : string
    {
        $formInputs = '';
        foreach ($reply->getFields() as $name => $value) {
            $formInputs .= sprintf(
                    '<input type="hidden" name="%1$s" value="%2$s" />',
                    htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
                ) . "\n";
        }

        $layout = <<<'HTML'
<form id="post_redirect" action="%1$s" method="post">
    <p>Redirecting to payment page...</p>
    <p>%2$s</p>
</form>

<script>$('#post_redirect').submit()</script>
HTML;

        return sprintf($layout, htmlspecialchars($reply->getUrl(), ENT_QUOTES, 'UTF-8'), $formInputs);
    }
}
