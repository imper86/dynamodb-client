<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class ContinuousBackupsDescription
{
    public function __construct(
        public readonly ?ContinuousBackupsStatus $continuousBackupsStatus = null,
        public readonly ?PointInTimeRecoveryDescription $pointInTimeRecoveryDescription = null,
    ) {}
}
