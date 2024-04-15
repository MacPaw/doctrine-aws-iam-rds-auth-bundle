<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Aws\Token;

use Aws\Credentials\CredentialProvider;
use Aws\Rds\AuthTokenGenerator;

class RdsTokenProvider implements TokenProviderInterface
{
    private readonly int $lifetime;
    private readonly AuthTokenGenerator $generator;

    public function __construct(int $lifetime)
    {
        $this->lifetime = $lifetime;
        $this->generator = new AuthTokenGenerator(CredentialProvider::defaultProvider());
    }

    public function getToken(string $endpoint, string $region, string $username, bool $refresh = false): string
    {
        return $this->generator->createToken($endpoint, $region, $username, $this->lifetime);
    }
}
