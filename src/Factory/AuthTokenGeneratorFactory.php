<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Factory;

use Aws\Credentials\CredentialProvider;
use Aws\Rds\AuthTokenGenerator;

final readonly class AuthTokenGeneratorFactory
{
    public function __invoke(): AuthTokenGenerator
    {
        return new AuthTokenGenerator(
            CredentialProvider::defaultProvider()
        );
    }
}
