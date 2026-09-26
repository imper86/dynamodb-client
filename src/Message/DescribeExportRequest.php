<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class DescribeExportRequest
{
    /**
     * @param non-empty-string $exportArn the ARN of the export to describe
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $exportArn,
    ) {
        Assert::stringNotEmpty($this->exportArn);
        Assert::minLength($this->exportArn, 37);
        Assert::maxLength($this->exportArn, 1024);
    }
}
