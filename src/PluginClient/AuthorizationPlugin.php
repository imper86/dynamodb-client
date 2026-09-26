<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\PluginClient;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use InvalidArgumentException;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Signer\SignatureV4;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

final class AuthorizationPlugin implements Plugin
{
    private readonly SignatureV4 $signer;

    /**
     * @param non-empty-string $region
     * @param non-empty-string $service
     * @throws MissingCredentialsException when no credentials are given and the environment does not provide any
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $region,
        ?Credentials $credentials = null,
        string $service = 'dynamodb',
    ) {
        $this->signer = new SignatureV4($credentials ?? Credentials::fromEnvironment(), $region, $service);
    }

    /**
     * @throws InvalidArgumentException when a header value is invalid
     * @throws RuntimeException when the request body cannot be read
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        return $next($this->signer->sign($request));
    }
}
