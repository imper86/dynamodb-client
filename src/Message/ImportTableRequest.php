<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\InputCompressionType;
use Imper86\DynamoDBClient\Model\InputFormat;
use Imper86\DynamoDBClient\Model\InputFormatOptions;
use Imper86\DynamoDBClient\Model\S3BucketSource;
use Imper86\DynamoDBClient\Model\TableCreationParameters;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class ImportTableRequest
{
    /**
     * @param TableCreationParameters $tableCreationParameters the table to create and import the data into
     * @param null|non-empty-string $clientToken makes the call idempotent for eight hours after it completes
     * @param null|InputFormatOptions $inputFormatOptions how a CSV source is laid out
     * @throws InvalidArgumentException
     */
    public function __construct(
        public InputFormat $inputFormat,
        public S3BucketSource $s3BucketSource,
        public TableCreationParameters $tableCreationParameters,
        public ?string $clientToken = null,
        public ?InputCompressionType $inputCompressionType = null,
        public ?InputFormatOptions $inputFormatOptions = null,
    ) {
        Assert::nullOrRegex($this->clientToken, '/^[^$]+$/');
    }
}
