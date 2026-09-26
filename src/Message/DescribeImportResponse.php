<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\ImportTableDescription;

final class DescribeImportResponse
{
    /**
     * The description is the whole payload: requiring it would turn an unexpectedly empty body into a
     * deserialization failure instead of a response the caller can inspect.
     */
    public function __construct(
        public readonly ?ImportTableDescription $importTableDescription = null,
    ) {}
}
