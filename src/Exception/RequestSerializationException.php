<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Exception;

use Exception;
use Throwable;

use function sprintf;

final class RequestSerializationException extends Exception implements ExceptionInterface
{
    public function __construct(
        public readonly ?object $payload,
        Throwable $previous
    ) {
        parent::__construct(
            sprintf('Failed to serialize request because of: %s', $previous->getMessage()),
            0,
            $previous,
        );
    }
}
