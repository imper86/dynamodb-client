<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Exception;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;

final class HttpClientException extends Exception implements ExceptionInterface
{
    public static function from(ClientExceptionInterface $exception): self
    {
        return new self($exception->getMessage(), $exception->getCode(), $exception);
    }
}
