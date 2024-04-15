<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Aws\Token;

interface TokenProviderInterface
{
    public function getToken(string $endpoint, string $region, string $username, bool $refresh = false): string;
}
