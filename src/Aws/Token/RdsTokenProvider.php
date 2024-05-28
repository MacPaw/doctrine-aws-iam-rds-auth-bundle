<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Aws\Token;

use Aws\Credentials\CredentialProvider;
use Aws\Rds\AuthTokenGenerator;

final readonly class RdsTokenProvider implements TokenProviderInterface
{
    private int $lifetime;
    private AuthTokenGenerator $generator;

    public function __construct(
        AuthTokenGenerator $generator,
        int $lifetime,
    ) {
        $this->lifetime = $lifetime;
        $this->generator = $generator;
    }

    public function getToken(string $endpoint, string $region, string $username, bool $refresh = false): string
    {
        return $this->generator->createToken($endpoint, $region, $username, $this->lifetime);
    }
}
