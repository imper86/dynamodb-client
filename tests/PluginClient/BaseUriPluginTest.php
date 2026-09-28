<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\PluginClient;

use Http\Discovery\Exception\NotFoundException;
use Http\Promise\FulfilledPromise;
use Http\Promise\Promise;
use Imper86\DynamoDBClient\PluginClient\BaseUriPlugin;
use InvalidArgumentException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

use function preg_quote;
use function putenv;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(BaseUriPlugin::class)]
final class BaseUriPluginTest extends TestCase
{
    protected function setUp(): void
    {
        $this->clearEnvironment();
    }

    protected function tearDown(): void
    {
        $this->clearEnvironment();
    }

    /**
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function testDefaultsToTheRegionalEndpoint(): void
    {
        self::assertSame('https://dynamodb.eu-central-1.amazonaws.com/', $this->resolve());
    }

    /**
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function testPrefersTheGivenEndpointOverTheEnvironment(): void
    {
        $this->setEnvironment(BaseUriPlugin::ENDPOINT_ENV_VARIABLE, 'http://service:8000');
        $this->setEnvironment(BaseUriPlugin::GLOBAL_ENDPOINT_ENV_VARIABLE, 'http://global:4566');

        self::assertSame('http://localhost:8000/', $this->resolve('http://localhost:8000'));
    }

    /**
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function testPrefersTheServiceSpecificVariableOverTheGlobalOne(): void
    {
        $this->setEnvironment(BaseUriPlugin::ENDPOINT_ENV_VARIABLE, 'http://service:8000');
        $this->setEnvironment(BaseUriPlugin::GLOBAL_ENDPOINT_ENV_VARIABLE, 'http://global:4566');

        self::assertSame('http://service:8000/', $this->resolve());
    }

    /**
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function testFallsBackToTheGlobalVariable(): void
    {
        $this->setEnvironment(BaseUriPlugin::GLOBAL_ENDPOINT_ENV_VARIABLE, 'http://global:4566');

        self::assertSame('http://global:4566/', $this->resolve());
    }

    /**
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function testTreatsAnEmptyVariableAsUnset(): void
    {
        $this->setEnvironment(BaseUriPlugin::ENDPOINT_ENV_VARIABLE, '');
        $this->setEnvironment(BaseUriPlugin::GLOBAL_ENDPOINT_ENV_VARIABLE, 'http://global:4566');

        self::assertSame('http://global:4566/', $this->resolve());
    }

    /**
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function testIgnoresTheEnvironmentWhenToldTo(): void
    {
        $this->setEnvironment(BaseUriPlugin::ENDPOINT_ENV_VARIABLE, 'http://service:8000');
        $this->setEnvironment(BaseUriPlugin::GLOBAL_ENDPOINT_ENV_VARIABLE, 'http://global:4566');
        $this->setEnvironment(BaseUriPlugin::IGNORE_CONFIGURED_ENDPOINTS_ENV_VARIABLE, 'TRUE');

        self::assertSame('https://dynamodb.eu-central-1.amazonaws.com/', $this->resolve());
    }

    /**
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function testStillUsesTheGivenEndpointWhenIgnoringTheEnvironment(): void
    {
        $this->setEnvironment(BaseUriPlugin::IGNORE_CONFIGURED_ENDPOINTS_ENV_VARIABLE, 'true');

        self::assertSame('http://localhost:8000/', $this->resolve('http://localhost:8000'));
    }

    /**
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function testReadsTheEnvironmentWhenTheIgnoreFlagIsFalse(): void
    {
        $this->setEnvironment(BaseUriPlugin::ENDPOINT_ENV_VARIABLE, 'http://service:8000');
        $this->setEnvironment(BaseUriPlugin::IGNORE_CONFIGURED_ENDPOINTS_ENV_VARIABLE, 'false');

        self::assertSame('http://service:8000/', $this->resolve());
    }

    /**
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function testPrependsThePathOfTheEndpoint(): void
    {
        self::assertSame('https://proxy.example.com/dynamodb/', $this->resolve('https://proxy.example.com/dynamodb'));
    }

    /**
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function testReplacesTheHostHeader(): void
    {
        self::assertSame('localhost:8000', $this->handleRequest('http://localhost:8000')->getHeaderLine('Host'));
    }

    /**
     * @return iterable<string, array{non-empty-string, non-empty-string}>
     */
    public static function invalidEndpoints(): iterable
    {
        yield 'no scheme' => ['localhost:8000', 'must be an absolute http or https url'];

        yield 'another scheme' => ['ftp://localhost:8000', 'must be an absolute http or https url'];

        yield 'unparseable' => ['http:///path', 'is not a valid url'];

        yield 'no host' => ['http:localhost', 'must have a host'];

        yield 'a query' => ['http://localhost:8000/?a=b', 'must not have a query'];

        yield 'a fragment' => ['http://localhost:8000/#top', 'must not have a fragment'];
    }

    /**
     * @param non-empty-string $endpoint
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    #[DataProvider('invalidEndpoints')]
    public function testRejectsAnInvalidGivenEndpoint(string $endpoint, string $message): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessageMatches(sprintf(
            '/^The endpoint "%s" %s\./',
            preg_quote($endpoint, '/'),
            preg_quote($message, '/'),
        ));

        $this->resolve($endpoint);
    }

    /**
     * @param non-empty-string $endpoint
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    #[DataProvider('invalidEndpoints')]
    public function testRejectsAnInvalidConfiguredEndpoint(string $endpoint, string $message): void
    {
        $this->setEnvironment(BaseUriPlugin::ENDPOINT_ENV_VARIABLE, $endpoint);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessageMatches(sprintf(
            '/^The endpoint from %s "%s" %s\./',
            BaseUriPlugin::ENDPOINT_ENV_VARIABLE,
            preg_quote($endpoint, '/'),
            preg_quote($message, '/'),
        ));

        $this->resolve();
    }

    /**
     * @param null|non-empty-string $endpoint
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    private function resolve(?string $endpoint = null): string
    {
        return $this->handleRequest($endpoint)->getUri()->__toString();
    }

    /**
     * @param null|non-empty-string $endpoint
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    private function handleRequest(?string $endpoint): RequestInterface
    {
        $handled = null;
        $next = static function (RequestInterface $request) use (&$handled): Promise {
            $handled = $request;

            return new FulfilledPromise(new Response());
        };

        (new BaseUriPlugin('eu-central-1', $endpoint, new Psr17Factory()))->handleRequest(
            new Request('POST', '/'),
            $next,
            static function (): never {
                self::fail('Must not restart the chain.');
            },
        );

        self::assertInstanceOf(RequestInterface::class, $handled);

        return $handled;
    }

    private function setEnvironment(string $name, string $value): void
    {
        putenv(sprintf('%s=%s', $name, $value));
    }

    private function clearEnvironment(): void
    {
        putenv(BaseUriPlugin::ENDPOINT_ENV_VARIABLE);
        putenv(BaseUriPlugin::GLOBAL_ENDPOINT_ENV_VARIABLE);
        putenv(BaseUriPlugin::IGNORE_CONFIGURED_ENDPOINTS_ENV_VARIABLE);
    }
}
