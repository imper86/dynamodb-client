<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Webmozart\Assert\Assert;

use function getenv;
use function is_string;

final class Credentials
{
    public const KEY_ENV_VARIABLE = 'AWS_ACCESS_KEY_ID';

    public const SECRET_ENV_VARIABLE = 'AWS_SECRET_ACCESS_KEY';

    public const TOKEN_ENV_VARIABLE = 'AWS_SESSION_TOKEN';

    /**
     * @param non-empty-string $key
     * @param non-empty-string $secret
     * @param null|non-empty-string $token session token of temporary credentials
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $key,
        public readonly string $secret,
        public readonly ?string $token = null,
    ) {
        Assert::stringNotEmpty($this->key);
        Assert::stringNotEmpty($this->secret);
        Assert::nullOrStringNotEmpty($this->token);
    }

    /**
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     */
    public static function fromEnvironment(): self
    {
        $key = self::readEnvVariable(self::KEY_ENV_VARIABLE);
        $secret = self::readEnvVariable(self::SECRET_ENV_VARIABLE);

        if (null === $key || null === $secret) {
            throw MissingCredentialsException::fromEnvironment([self::KEY_ENV_VARIABLE, self::SECRET_ENV_VARIABLE]);
        }

        return new self($key, $secret, self::readEnvVariable(self::TOKEN_ENV_VARIABLE));
    }

    /**
     * @return null|non-empty-string
     */
    private static function readEnvVariable(string $name): ?string
    {
        $value = getenv($name);

        if (!is_string($value) || '' === $value) {
            return null;
        }

        return $value;
    }
}
