<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\PluginClient;

use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use Http\Client\Common\PluginClient;
use Http\Client\Common\PluginClientBuilder;
use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr18ClientDiscovery;
use InvalidArgumentException;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Model\Credentials;
use Psr\Http\Client\ClientInterface;

final class PluginClientFactory
{
    /**
     * @param non-empty-string $region
     * @param null|non-empty-string $appId an opaque identifier of your application, reported in the
     *                                     user agent, falls back to the AWS_SDK_UA_APP_ID variable
     * @param null|non-empty-string $endpoint an absolute http(s) url to send requests to instead of the
     *                                        regional AWS endpoint, such as `http://localhost:8000` for
     *                                        DynamoDB Local, see BaseUriPlugin for the fallbacks
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException when no credentials are given and the environment does not provide any
     * @throws NotFoundException
     */
    public static function create(
        string $region,
        ?Credentials $credentials = null,
        ?ClientInterface $client = null,
        ?string $appId = null,
        ?string $endpoint = null,
    ): PluginClient {
        return (new PluginClientBuilder())
            ->addPlugin(new BaseUriPlugin($region, $endpoint))
            ->addPlugin(new HeaderDefaultsPlugin([
                'Accept-Encoding' => 'identity',
                'Content-Type' => 'application/x-amz-json-1.0',
            ]))
            ->addPlugin(new AuthorizationPlugin($region, $credentials))
            // The user agent is never signed, so it is set once the request is final.
            ->addPlugin(new UserAgentPlugin($appId))
            ->createClient($client ?? Psr18ClientDiscovery::find())
        ;
    }
}
