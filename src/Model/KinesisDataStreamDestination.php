<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class KinesisDataStreamDestination
{
    /**
     * `ApproximateCreationDateTimePrecision` is the precision of the timestamp on each record the table
     * puts on the stream.
     *
     * @param null|string $destinationStatusDescription a human-readable account of the status, such as why
     *                                                  enabling failed
     */
    public function __construct(
        public ?ApproximateCreationDateTimePrecision $approximateCreationDateTimePrecision = null,
        public ?DestinationStatus $destinationStatus = null,
        public ?string $destinationStatusDescription = null,
        public ?string $streamArn = null,
    ) {}
}
