<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\PluginClient;

use Http\Promise\FulfilledPromise;
use Http\Promise\Promise;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use InvalidArgumentException;
use RuntimeException;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\PluginClient\AuthorizationPlugin;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

use function putenv;

/**
 * @internal
 */
#[CoversClass(AuthorizationPlugin::class)]
final class AuthorizationPluginTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws RuntimeException
     */
    public function testSignsTheRequestBeforePassingItToTheNextPlugin(): void
    {
        $plugin = new AuthorizationPlugin('eu-central-1', new Credentials('AKIDEXAMPLE', 'secret'));
        $request = new Request('POST', 'https://dynamodb.eu-central-1.amazonaws.com/', [
            'Content-Type' => 'application/x-amz-json-1.0',
            'X-Amz-Target' => 'DynamoDB_20120810.GetItem',
        ], '{"TableName":"Thread"}');

        $signed = null;
        $next = static function (RequestInterface $request) use (&$signed): Promise {
            $signed = $request;

            return new FulfilledPromise(new Response());
        };

        $plugin->handleRequest($request, $next, static fn(): never => self::fail('Must not restart the chain.'));

        self::assertInstanceOf(RequestInterface::class, $signed);
        self::assertMatchesRegularExpression(
            '#^AWS4-HMAC-SHA256 Credential=AKIDEXAMPLE/\d{8}/eu-central-1/dynamodb/aws4_request, '
            . 'SignedHeaders=content-type;host;x-amz-date;x-amz-target, Signature=[0-9a-f]{64}$#',
            $signed->getHeaderLine('Authorization'),
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws RuntimeException
     */
    public function testFallsBackToTheEnvironmentWhenNoCredentialsAreGiven(): void
    {
        putenv(Credentials::KEY_ENV_VARIABLE . '=AKIDEXAMPLE');
        putenv(Credentials::SECRET_ENV_VARIABLE . '=secret');

        try {
            $plugin = new AuthorizationPlugin('eu-central-1');
        } finally {
            putenv(Credentials::KEY_ENV_VARIABLE);
            putenv(Credentials::SECRET_ENV_VARIABLE);
        }

        $signed = null;
        $next = static function (RequestInterface $request) use (&$signed): Promise {
            $signed = $request;

            return new FulfilledPromise(new Response());
        };

        $plugin->handleRequest(
            new Request('POST', 'https://dynamodb.eu-central-1.amazonaws.com/'),
            $next,
            static fn(): never => self::fail('Must not restart the chain.'),
        );

        self::assertInstanceOf(RequestInterface::class, $signed);
        self::assertStringContainsString('Credential=AKIDEXAMPLE/', $signed->getHeaderLine('Authorization'));
    }

    /**
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws RuntimeException
     */
    public function testFailsWhenNeitherCredentialsNorEnvironmentAreAvailable(): void
    {
        putenv(Credentials::KEY_ENV_VARIABLE);
        putenv(Credentials::SECRET_ENV_VARIABLE);

        self::expectException(MissingCredentialsException::class);

        new AuthorizationPlugin('eu-central-1');
    }
}
