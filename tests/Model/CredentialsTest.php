<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClientTests\Model;

use InvalidArgumentException;
use OoAws\DynamoDBClient\Exception\MissingCredentialsException;
use OoAws\DynamoDBClient\Model\Credentials;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function putenv;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(Credentials::class)]
final class CredentialsTest extends TestCase
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
     * @throws MissingCredentialsException
     */
    public function testReadsCredentialsFromEnvironment(): void
    {
        $this->setEnvironment(Credentials::KEY_ENV_VARIABLE, 'AKIDEXAMPLE');
        $this->setEnvironment(Credentials::SECRET_ENV_VARIABLE, 'secret');

        $credentials = Credentials::fromEnvironment();

        self::assertSame('AKIDEXAMPLE', $credentials->key);
        self::assertSame('secret', $credentials->secret);
        self::assertNull($credentials->token);
    }

    /**
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     */
    public function testReadsSessionTokenFromEnvironment(): void
    {
        $this->setEnvironment(Credentials::KEY_ENV_VARIABLE, 'AKIDEXAMPLE');
        $this->setEnvironment(Credentials::SECRET_ENV_VARIABLE, 'secret');
        $this->setEnvironment(Credentials::TOKEN_ENV_VARIABLE, 'token');

        self::assertSame('token', Credentials::fromEnvironment()->token);
    }

    /**
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     */
    public function testFailsWhenTheEnvironmentDoesNotProvideCredentials(): void
    {
        $this->setEnvironment(Credentials::KEY_ENV_VARIABLE, 'AKIDEXAMPLE');
        $this->setEnvironment(Credentials::SECRET_ENV_VARIABLE, '');

        self::expectException(MissingCredentialsException::class);

        Credentials::fromEnvironment();
    }

    private function setEnvironment(string $name, string $value): void
    {
        putenv(sprintf('%s=%s', $name, $value));
    }

    private function clearEnvironment(): void
    {
        putenv(Credentials::KEY_ENV_VARIABLE);
        putenv(Credentials::SECRET_ENV_VARIABLE);
        putenv(Credentials::TOKEN_ENV_VARIABLE);
    }
}
