<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class S3BucketSource
{
    /**
     * @param non-empty-string $s3Bucket
     * @param null|non-empty-string $s3BucketOwner the twelve-digit account ID owning the bucket, when it is
     *                                             not the caller's
     * @param null|string $s3KeyPrefix the key prefix shared by every file to import
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $s3Bucket,
        public ?string $s3BucketOwner = null,
        public ?string $s3KeyPrefix = null,
    ) {
        Assert::stringNotEmpty($this->s3Bucket);
        Assert::maxLength($this->s3Bucket, 255);
        Assert::regex($this->s3Bucket, '/^[a-z0-9A-Z]+[.\-\w]*[a-z0-9A-Z]+$/');
        Assert::nullOrRegex($this->s3BucketOwner, '/^[0-9]{12}$/');
        Assert::nullOrMaxLength($this->s3KeyPrefix, 1024);
    }
}
