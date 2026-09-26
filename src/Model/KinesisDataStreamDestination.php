<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class KinesisDataStreamDestination
{
    /**
     * `ApproximateCreationDateTimePrecision` is the precision of the timestamp on each record the table
     * puts on the stream.
     *
     * @param null|string $destinationStatusDescription a human-readable account of the status, such as why
     *                                                  enabling failed
     */
    public function __construct(
        public readonly ?ApproximateCreationDateTimePrecision $approximateCreationDateTimePrecision = null,
        public readonly ?DestinationStatus $destinationStatus = null,
        public readonly ?string $destinationStatusDescription = null,
        public readonly ?string $streamArn = null,
    ) {}
}
