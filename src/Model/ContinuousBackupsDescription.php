<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class ContinuousBackupsDescription
{
    public function __construct(
        public ?ContinuousBackupsStatus $continuousBackupsStatus = null,
        public ?PointInTimeRecoveryDescription $pointInTimeRecoveryDescription = null,
    ) {}
}
