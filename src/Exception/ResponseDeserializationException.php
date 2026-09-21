<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Exception;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Throwable;

use function sprintf;

final class ResponseDeserializationException extends Exception implements ExceptionInterface
{
    public function __construct(
        public readonly ResponseInterface $response,
        Throwable $previous,
    ) {
        parent::__construct(
            sprintf('Failed to deserialize response because of: %s', $previous->getMessage()),
            0,
            $previous,
        );
    }
}
