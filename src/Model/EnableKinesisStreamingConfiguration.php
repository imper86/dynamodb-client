<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class EnableKinesisStreamingConfiguration
{
    /**
     * `ApproximateCreationDateTimePrecision` is the precision of the timestamp on each record the table
     * puts on the stream.
     */
    public function __construct(
        public readonly ?ApproximateCreationDateTimePrecision $approximateCreationDateTimePrecision = null,
    ) {}
}
