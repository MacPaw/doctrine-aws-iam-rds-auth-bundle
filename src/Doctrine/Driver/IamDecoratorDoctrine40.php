<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Doctrine\Driver;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\ServerVersionProvider;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Aws\Token\TokenProviderInterface;

readonly class IamDecoratorDoctrine40 implements IamDecoratorInterface
{
    public function __construct(
        private Driver $subject,
        private TokenProviderInterface $tokenProvider,
        private string $region,
    ) {
    }

    public function connect(array $params): DriverConnection
    {
        // @phpstan-ignore-next-line
        $host = $params['host'];
        // @phpstan-ignore-next-line
        $user = $params['user'];
        // @phpstan-ignore-next-line
        $port = $params['port'];

        $params['password'] = $this->tokenProvider->getToken(
            "$host:$port",
            $this->region,
            $user,
        );

        try {
            return $this->subject->connect($params);
        } catch (\Throwable $e) {
            if ($this->isConnectionException($e)) {
                $params['password'] = $this->tokenProvider->getToken(
                    "$host:$port",
                    $this->region,
                    $user,
                    true
                );

                return $this->subject->connect($params);
            }

            throw $e;
        }
    }

    public function getDatabasePlatform(ServerVersionProvider $versionProvider): AbstractPlatform
    {
        return $this->subject->getDatabasePlatform($versionProvider);
    }

    public function getExceptionConverter(): ExceptionConverter
    {
        return $this->subject->getExceptionConverter();
    }

    private function isConnectionException(\Throwable $e): bool
    {
        if ($e instanceof DriverException) {
            $driverException = $this->getExceptionConverter()->convert($e, null);

            return $driverException instanceof ConnectionException;
        }

        return false;
    }
}
