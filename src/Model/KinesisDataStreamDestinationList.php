<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<KinesisDataStreamDestination>
 */
final readonly class KinesisDataStreamDestinationList extends AbstractObjectList
{
    /**
     * @return class-string<KinesisDataStreamDestination>
     */
    public static function itemType(): string
    {
        return KinesisDataStreamDestination::class;
    }
}
