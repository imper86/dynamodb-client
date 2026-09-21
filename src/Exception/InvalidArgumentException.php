<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient\Exception;

final class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
    public static function from(\InvalidArgumentException $exception): self
    {
        return new self($exception->getMessage(), $exception->getCode(), $exception);
    }
}
