<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class DescribeImportRequest
{
    /**
     * @param non-empty-string $importArn the ARN of the import to describe
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $importArn,
    ) {
        Assert::stringNotEmpty($this->importArn);
        Assert::minLength($this->importArn, 37);
        Assert::maxLength($this->importArn, 1024);
    }
}
