<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\PluginClient;

use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use Http\Client\Common\PluginClient;
use Http\Client\Common\PluginClientBuilder;
use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use InvalidArgumentException;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Model\Credentials;
use Psr\Http\Client\ClientInterface;

use function sprintf;

final readonly class PluginClientFactory
{
    /**
     * @param non-empty-string $region
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException when no credentials are given and the environment does not provide any
     * @throws NotFoundException
     */
    public static function create(
        string $region,
        ?Credentials $credentials = null,
        ?ClientInterface $client = null,
    ): PluginClient {
        $uriFactory = Psr17FactoryDiscovery::findUriFactory();
        $baseUri = $uriFactory->createUri(sprintf('https://dynamodb.%s.amazonaws.com', $region));

        return new PluginClientBuilder()
            ->addPlugin(new BaseUriPlugin($baseUri))
            ->addPlugin(new HeaderDefaultsPlugin([
                'Accept-Encoding' => 'identity',
                'Content-Type' => 'application/x-amz-json-1.0',
            ]))
            ->addPlugin(new AuthorizationPlugin($region, $credentials))
            ->createClient($client ?? Psr18ClientDiscovery::find())
        ;
    }
}
