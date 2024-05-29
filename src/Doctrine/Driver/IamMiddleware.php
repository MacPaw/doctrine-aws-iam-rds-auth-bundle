<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Doctrine\Driver;

use Macpaw\DoctrineAwsIamRdsAuthBundle\Aws\Token\TokenProviderInterface;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;

readonly class IamMiddleware implements Middleware
{
    public function __construct(
        private TokenProviderInterface $tokenProvider,
        private string $region,
        private bool $useIam,
    ) {
    }

    public function wrap(Driver $driver): Driver
    {
        if ($this->useIam) {
            return new IamDecorator($driver, $this->tokenProvider, $this->region);
        }

        return $driver;
    }
}
