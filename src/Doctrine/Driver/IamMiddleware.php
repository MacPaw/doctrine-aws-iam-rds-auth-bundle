<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Doctrine\Driver;

use Macpaw\DoctrineAwsIamRdsAuthBundle\Aws\Token\TokenProviderInterface;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;

class IamMiddleware implements Middleware
{
    public function __construct(
        private readonly TokenProviderInterface $tokenProvider,
        private readonly string $region,
        private readonly bool $useIam,
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
