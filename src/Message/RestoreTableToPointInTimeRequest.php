<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use DateTimeImmutable;
use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\BillingMode;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndexList;
use Imper86\DynamoDBClient\Model\LocalSecondaryIndexList;
use Imper86\DynamoDBClient\Model\OnDemandThroughput;
use Imper86\DynamoDBClient\Model\ProvisionedThroughput;
use Imper86\DynamoDBClient\Model\SSESpecification;
use Imper86\DynamoDBClient\Model\VectorIndexList;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Webmozart\Assert\Assert;

final readonly class RestoreTableToPointInTimeRequest
{
    /**
     * Name the source table by `SourceTableArn` or by `SourceTableName`, and the point in time by
     * `RestoreDateTime` or by `UseLatestRestorableTime`.
     *
     * @param non-empty-string $targetTableName the name of the new table; it cannot be an ARN
     * @param null|GlobalSecondaryIndexList $globalSecondaryIndexOverride the indexes may not carry a
     *                                                                    `WarmThroughput`
     * @param null|DateTimeImmutable $restoreDateTime the point in the past to restore the table as of
     * @param null|non-empty-string $sourceTableArn
     * @param null|non-empty-string $sourceTableName
     * @param null|bool $useLatestRestorableTime true to restore as of `LatestRestorableDateTime`, typically five
     *                                           minutes ago
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $targetTableName,
        public ?BillingMode $billingModeOverride = null,
        public ?GlobalSecondaryIndexList $globalSecondaryIndexOverride = null,
        public ?LocalSecondaryIndexList $localSecondaryIndexOverride = null,
        public ?OnDemandThroughput $onDemandThroughputOverride = null,
        public ?ProvisionedThroughput $provisionedThroughputOverride = null,
        public ?DateTimeImmutable $restoreDateTime = null,
        public ?string $sourceTableArn = null,
        public ?string $sourceTableName = null,
        #[SerializedName('SSESpecificationOverride')]
        public ?SSESpecification $sseSpecificationOverride = null,
        public ?bool $useLatestRestorableTime = null,
        public ?VectorIndexList $vectorIndexOverride = null,
    ) {
        Assert::stringNotEmpty($this->targetTableName);
        Assert::minLength($this->targetTableName, 3);
        Assert::maxLength($this->targetTableName, 255);
        Assert::regex($this->targetTableName, '/^[a-zA-Z0-9_.\-]+$/');
        Assert::nullOrStringNotEmpty($this->sourceTableArn);
        Assert::nullOrMaxLength($this->sourceTableArn, 1024);
        Assert::nullOrStringNotEmpty($this->sourceTableName);
        Assert::nullOrMinLength($this->sourceTableName, 3);
        Assert::nullOrMaxLength($this->sourceTableName, 255);
        Assert::nullOrRegex($this->sourceTableName, '/^[a-zA-Z0-9_.\-]+$/');

        foreach ($this->globalSecondaryIndexOverride ?? [] as $index) {
            Assert::null(
                $index->warmThroughput,
                'RestoreTableToPointInTime does not accept a WarmThroughput on a global secondary index.',
            );
        }
    }
}
