<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class Endpoint
{
    /**
     * @param null|string $address the endpoint's address, which the reference calls an IP address but
     *                             the service returns as a host name
     * @param null|int $cachePeriodInMinutes how long the address may be cached
     */
    public function __construct(
        public readonly ?string $address = null,
        public readonly ?int $cachePeriodInMinutes = null,
    ) {}
}
