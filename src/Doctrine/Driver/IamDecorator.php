<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Doctrine\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Aws\Token\TokenProviderInterface;

readonly class IamDecorator implements IamDecoratorInterface
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
                    true,
                );

                return $this->subject->connect($params);
            }

            throw $e;
        }
    }

    private function isConnectionException(\Throwable $e): bool
    {
        if ($e instanceof DriverException) {
            $driverException = $this->getExceptionConverter()->convert($e, null);

            return $driverException instanceof ConnectionException;
        }

        return false;
    }

    public function getExceptionConverter(): ExceptionConverter
    {
        return $this->subject->getExceptionConverter();
    }

    public function getDatabasePlatform(): AbstractPlatform
    {
        return $this->subject->getDatabasePlatform();
    }

    /**
     * @return AbstractSchemaManager<PostgreSQLPlatform>
     */
    public function getSchemaManager(Connection $conn, AbstractPlatform $platform): AbstractSchemaManager
    {
        return $this->subject->getSchemaManager($conn, $platform);
    }
}
