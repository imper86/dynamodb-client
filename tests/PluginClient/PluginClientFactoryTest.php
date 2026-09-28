<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\PluginClient;

use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\PluginClient\PluginClientFactory;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
#[CoversClass(PluginClientFactory::class)]
final class PluginClientFactoryTest extends TestCase
{
    /**
     * @throws ClientExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSendsRequestsToTheRegionalEndpoint(): void
    {
        $sent = $this->send(null);

        self::assertSame('https://dynamodb.eu-central-1.amazonaws.com/', $sent->getUri()->__toString());
        self::assertSame('dynamodb.eu-central-1.amazonaws.com', $sent->getHeaderLine('Host'));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSendsSignedRequestsToACustomEndpoint(): void
    {
        $sent = $this->send('http://localhost:8000');

        self::assertSame('http://localhost:8000/', $sent->getUri()->__toString());
        self::assertSame('localhost:8000', $sent->getHeaderLine('Host'));
        self::assertStringStartsWith(
            'AWS4-HMAC-SHA256 Credential=AKIDEXAMPLE/',
            $sent->getHeaderLine('Authorization'),
        );
    }

    /**
     * @param null|non-empty-string $endpoint
     * @throws ClientExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    private function send(?string $endpoint): RequestInterface
    {
        $httpClient = new MockClient();

        PluginClientFactory::create(
            'eu-central-1',
            new Credentials('AKIDEXAMPLE', 'secret'),
            $httpClient,
            endpoint: $endpoint,
        )->sendRequest(new Request('POST', '/'));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(RequestInterface::class, $sent);

        return $sent;
    }
}
