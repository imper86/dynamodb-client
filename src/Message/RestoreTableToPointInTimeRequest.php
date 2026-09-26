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

use function str_contains;

final class RestoreTableToPointInTimeRequest
{
    /**
     * Name the source table by `SourceTableArn` or by `SourceTableName`, and the point in time by
     * `RestoreDateTime` or by `UseLatestRestorableTime`; {@see self::at()} and {@see self::latest()} make
     * both choices for you.
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
        public readonly string $targetTableName,
        public readonly ?BillingMode $billingModeOverride = null,
        public readonly ?GlobalSecondaryIndexList $globalSecondaryIndexOverride = null,
        public readonly ?LocalSecondaryIndexList $localSecondaryIndexOverride = null,
        public readonly ?OnDemandThroughput $onDemandThroughputOverride = null,
        public readonly ?ProvisionedThroughput $provisionedThroughputOverride = null,
        public readonly ?DateTimeImmutable $restoreDateTime = null,
        public readonly ?string $sourceTableArn = null,
        public readonly ?string $sourceTableName = null,
        #[SerializedName('SSESpecificationOverride')]
        public readonly ?SSESpecification $sseSpecificationOverride = null,
        public readonly ?bool $useLatestRestorableTime = null,
        public readonly ?VectorIndexList $vectorIndexOverride = null,
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

    /**
     * Restores the table as it was at a point in the past.
     *
     * @param non-empty-string $sourceTable the name or the ARN of the table to restore
     * @param non-empty-string $targetTableName the name of the new table; it cannot be an ARN
     * @throws InvalidArgumentException
     */
    public static function at(
        string $sourceTable,
        string $targetTableName,
        DateTimeImmutable $restoreDateTime,
        ?BillingMode $billingModeOverride = null,
        ?GlobalSecondaryIndexList $globalSecondaryIndexOverride = null,
        ?LocalSecondaryIndexList $localSecondaryIndexOverride = null,
        ?OnDemandThroughput $onDemandThroughputOverride = null,
        ?ProvisionedThroughput $provisionedThroughputOverride = null,
        ?SSESpecification $sseSpecificationOverride = null,
        ?VectorIndexList $vectorIndexOverride = null,
    ): self {
        return new self(
            targetTableName: $targetTableName,
            billingModeOverride: $billingModeOverride,
            globalSecondaryIndexOverride: $globalSecondaryIndexOverride,
            localSecondaryIndexOverride: $localSecondaryIndexOverride,
            onDemandThroughputOverride: $onDemandThroughputOverride,
            provisionedThroughputOverride: $provisionedThroughputOverride,
            restoreDateTime: $restoreDateTime,
            sourceTableArn: self::sourceTableArn($sourceTable),
            sourceTableName: self::sourceTableName($sourceTable),
            sseSpecificationOverride: $sseSpecificationOverride,
            vectorIndexOverride: $vectorIndexOverride,
        );
    }

    /**
     * Restores the table as it was at `LatestRestorableDateTime`, typically five minutes ago.
     *
     * @param non-empty-string $sourceTable the name or the ARN of the table to restore
     * @param non-empty-string $targetTableName the name of the new table; it cannot be an ARN
     * @throws InvalidArgumentException
     */
    public static function latest(
        string $sourceTable,
        string $targetTableName,
        ?BillingMode $billingModeOverride = null,
        ?GlobalSecondaryIndexList $globalSecondaryIndexOverride = null,
        ?LocalSecondaryIndexList $localSecondaryIndexOverride = null,
        ?OnDemandThroughput $onDemandThroughputOverride = null,
        ?ProvisionedThroughput $provisionedThroughputOverride = null,
        ?SSESpecification $sseSpecificationOverride = null,
        ?VectorIndexList $vectorIndexOverride = null,
    ): self {
        return new self(
            targetTableName: $targetTableName,
            billingModeOverride: $billingModeOverride,
            globalSecondaryIndexOverride: $globalSecondaryIndexOverride,
            localSecondaryIndexOverride: $localSecondaryIndexOverride,
            onDemandThroughputOverride: $onDemandThroughputOverride,
            provisionedThroughputOverride: $provisionedThroughputOverride,
            sourceTableArn: self::sourceTableArn($sourceTable),
            sourceTableName: self::sourceTableName($sourceTable),
            sseSpecificationOverride: $sseSpecificationOverride,
            useLatestRestorableTime: true,
            vectorIndexOverride: $vectorIndexOverride,
        );
    }

    /**
     * A table name cannot hold a colon and an ARN always does, so the colon tells the two apart.
     *
     * @param non-empty-string $sourceTable
     * @return null|non-empty-string
     */
    private static function sourceTableArn(string $sourceTable): ?string
    {
        return str_contains($sourceTable, ':') ? $sourceTable : null;
    }

    /**
     * @param non-empty-string $sourceTable
     * @return null|non-empty-string
     */
    private static function sourceTableName(string $sourceTable): ?string
    {
        return str_contains($sourceTable, ':') ? null : $sourceTable;
    }
}
