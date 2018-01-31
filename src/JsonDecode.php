<?php
declare(strict_types=1);

namespace App;

use Webmozart\Json\JsonDecoder;
use Webmozart\Json\JsonValidator;

class JsonDecode
{
    /**
     * @param $content
     * @param $schema
     *
     * @return mixed
     * @throws \Webmozart\Json\ValidationFailedException
     */
    public function decode($content, $schema)
    {
        $data = (new JsonDecoder())->decode($content);

        $errors = (new JsonValidator())->validate($data, $schema);

        if ($errors) {
            throw new InvalidJsonException($this->parseErrors($errors));
        }

        $decoder = new JsonDecoder();
        $decoder->setObjectDecoding(JsonDecoder::ASSOC_ARRAY);

        return $decoder->decode($content);
    }

    private function parseErrors(array $errors) : array
    {
        $parsedErrors = [];
        foreach ($errors as $error) {
            list($property, $message) = explode(': ', $error, 2);

            $parsedErrors[$property][] = $message;
        }

        return $parsedErrors;
    }
}
