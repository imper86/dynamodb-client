<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<Endpoint>
 */
final class EndpointList extends AbstractObjectList
{
    /**
     * @return class-string<Endpoint>
     */
    public static function itemType(): string
    {
        return Endpoint::class;
    }
}
