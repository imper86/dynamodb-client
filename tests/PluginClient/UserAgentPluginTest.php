<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\PluginClient;

use Http\Promise\FulfilledPromise;
use Http\Promise\Promise;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use InvalidArgumentException;
use Imper86\DynamoDBClient\PluginClient\UserAgentPlugin;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

use function preg_quote;
use function putenv;
use function str_repeat;

/**
 * @internal
 */
#[CoversClass(UserAgentPlugin::class)]
final class UserAgentPluginTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsTheFieldsInTheOrderAwsExpects(): void
    {
        putenv(UserAgentPlugin::APP_ID_ENV_VARIABLE);
        putenv(UserAgentPlugin::EXECUTION_ENV_VARIABLE);

        self::assertMatchesRegularExpression(
            '#^imper86-dynamodb-client/\S+ ua/2\.1 api/dynamodb\#\S+ '
            . 'os/(macos|linux|windows|other)(\#\S+)?( md/\S+)* lang/php\#'
            . preg_quote(PHP_VERSION, '#') . '$#',
            $this->handleRequest(new UserAgentPlugin()),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testReportsTheExecutionEnvironment(): void
    {
        putenv(UserAgentPlugin::EXECUTION_ENV_VARIABLE . '=AWS_Lambda_php');

        try {
            $userAgent = $this->handleRequest(new UserAgentPlugin());
        } finally {
            putenv(UserAgentPlugin::EXECUTION_ENV_VARIABLE);
        }

        self::assertStringContainsString(' exec-env/AWS_Lambda_php ', $userAgent . ' ');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testKeepsAUserAgentThatIsAlreadyOnTheRequest(): void
    {
        $userAgent = $this->handleRequest(
            new UserAgentPlugin(),
            new Request('POST', 'https://dynamodb.eu-central-1.amazonaws.com/', [
                'User-Agent' => 'my-framework/2.1',
            ]),
        );

        self::assertStringStartsWith('imper86-dynamodb-client/', $userAgent);
        self::assertStringEndsWith(' my-framework/2.1', $userAgent);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testReportsTheApplicationIdWithDisallowedCharactersReplaced(): void
    {
        self::assertStringEndsWith(
            ' app/my-app--v1.0-',
            $this->handleRequest(new UserAgentPlugin('my app (v1.0)')),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testReadsTheApplicationIdFromTheEnvironment(): void
    {
        putenv(UserAgentPlugin::APP_ID_ENV_VARIABLE . '=my-app');

        try {
            $userAgent = $this->handleRequest(new UserAgentPlugin());
        } finally {
            putenv(UserAgentPlugin::APP_ID_ENV_VARIABLE);
        }

        self::assertStringEndsWith(' app/my-app', $userAgent);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testOmitsTheApplicationIdWhenThereIsNone(): void
    {
        putenv(UserAgentPlugin::APP_ID_ENV_VARIABLE);

        self::assertStringNotContainsString(' app/', $this->handleRequest(new UserAgentPlugin()));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testFailsWhenTheApplicationIdIsTooLong(): void
    {
        self::expectException(InvalidArgumentException::class);

        new UserAgentPlugin(str_repeat('a', UserAgentPlugin::APP_ID_MAX_LENGTH + 1));
    }

    /**
     * @throws InvalidArgumentException
     */
    private function handleRequest(UserAgentPlugin $plugin, ?RequestInterface $request = null): string
    {
        $handled = null;
        $next = static function (RequestInterface $request) use (&$handled): Promise {
            $handled = $request;

            return new FulfilledPromise(new Response());
        };

        $plugin->handleRequest(
            $request ?? new Request('POST', 'https://dynamodb.eu-central-1.amazonaws.com/'),
            $next,
            static function (): never {
                self::fail('Must not restart the chain.');
            },
        );

        self::assertInstanceOf(RequestInterface::class, $handled);

        return $handled->getHeaderLine('User-Agent');
    }
}
