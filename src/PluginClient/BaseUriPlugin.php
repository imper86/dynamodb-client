<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\PluginClient;

use Http\Client\Common\Plugin;
use Http\Client\Common\Plugin\BaseUriPlugin as HttpBaseUriPlugin;
use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Promise\Promise;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Webmozart\Assert\Assert;

use function getenv;
use function is_string;
use function sprintf;
use function strtolower;

/**
 * Sends every request to the DynamoDB endpoint, chosen with the precedence of the AWS SDKs.
 *
 * An endpoint given in code wins. Otherwise the service specific AWS_ENDPOINT_URL_DYNAMODB applies,
 * then the global AWS_ENDPOINT_URL, and last the regional AWS endpoint. Setting
 * AWS_IGNORE_CONFIGURED_ENDPOINT_URLS to `true` disables both environment variables.
 *
 * @see https://docs.aws.amazon.com/sdkref/latest/guide/feature-ss-endpoints.html
 */
final class BaseUriPlugin implements Plugin
{
    public const ENDPOINT_ENV_VARIABLE = 'AWS_ENDPOINT_URL_DYNAMODB';

    public const GLOBAL_ENDPOINT_ENV_VARIABLE = 'AWS_ENDPOINT_URL';

    public const IGNORE_CONFIGURED_ENDPOINTS_ENV_VARIABLE = 'AWS_IGNORE_CONFIGURED_ENDPOINT_URLS';

    private const SCHEMES = ['http', 'https'];

    private readonly HttpBaseUriPlugin $plugin;

    /**
     * @param non-empty-string $region
     * @param null|non-empty-string $endpoint an absolute http(s) url, such as `http://localhost:8000`
     * @throws InvalidArgumentException when the endpoint is not an absolute http(s) url
     * @throws NotFoundException when no uri factory is given and none can be discovered
     */
    public function __construct(
        string $region,
        ?string $endpoint = null,
        ?UriFactoryInterface $uriFactory = null,
    ) {
        $this->plugin = new HttpBaseUriPlugin(
            $this->resolve($uriFactory ?? Psr17FactoryDiscovery::findUriFactory(), $region, $endpoint),
        );
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        return $this->plugin->handleRequest($request, $next, $first);
    }

    /**
     * @param non-empty-string $region
     * @param null|non-empty-string $endpoint
     * @throws InvalidArgumentException
     */
    private function resolve(UriFactoryInterface $uriFactory, string $region, ?string $endpoint): UriInterface
    {
        Assert::nullOrStringNotEmpty($endpoint);

        if (null !== $endpoint) {
            return $this->createEndpoint($uriFactory, $endpoint, 'The endpoint');
        }

        if (!$this->ignoresConfiguredEndpoints()) {
            foreach ([self::ENDPOINT_ENV_VARIABLE, self::GLOBAL_ENDPOINT_ENV_VARIABLE] as $variable) {
                $configured = $this->readEnvVariable($variable);

                if (null !== $configured) {
                    return $this->createEndpoint(
                        $uriFactory,
                        $configured,
                        sprintf('The endpoint from %s', $variable),
                    );
                }
            }
        }

        return $uriFactory->createUri(sprintf('https://dynamodb.%s.amazonaws.com', $region));
    }

    /**
     * @throws InvalidArgumentException
     */
    private function createEndpoint(UriFactoryInterface $uriFactory, string $endpoint, string $source): UriInterface
    {
        try {
            $uri = $uriFactory->createUri($endpoint);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException(
                sprintf('%s "%s" is not a valid url.', $source, $endpoint),
                0,
                $exception,
            );
        }

        Assert::oneOf(
            strtolower($uri->getScheme()),
            self::SCHEMES,
            sprintf('%s "%s" must be an absolute http or https url.', $source, $endpoint),
        );
        Assert::stringNotEmpty(
            $uri->getHost(),
            sprintf('%s "%s" must have a host.', $source, $endpoint),
        );
        Assert::isEmpty(
            $uri->getQuery(),
            sprintf('%s "%s" must not have a query.', $source, $endpoint),
        );
        Assert::isEmpty(
            $uri->getFragment(),
            sprintf('%s "%s" must not have a fragment.', $source, $endpoint),
        );

        return $uri;
    }

    private function ignoresConfiguredEndpoints(): bool
    {
        return 'true' === strtolower($this->readEnvVariable(self::IGNORE_CONFIGURED_ENDPOINTS_ENV_VARIABLE) ?? '');
    }

    /**
     * @return null|non-empty-string
     */
    private function readEnvVariable(string $name): ?string
    {
        $value = getenv($name);

        if (!is_string($value) || '' === $value) {
            return null;
        }

        return $value;
    }
}
