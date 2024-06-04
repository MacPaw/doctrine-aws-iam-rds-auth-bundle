<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Tests\Unit;

use Aws\Rds\AuthTokenGenerator;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\ConnectionLost;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Query;
use Doctrine\DBAL\ServerVersionProvider;
use InvalidArgumentException;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Aws\Token\RdsTokenProvider;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Aws\Token\RdsTokenProviderCacheDecorator;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Cache\CacheStorageInterface;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Doctrine\Driver\IamDecoratorDoctrine30;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Tests\AbstractDoctrineTestCase;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\MockObject\MockObject;

final class IamDecoratorDoctrine30Test extends AbstractDoctrineTestCase
{
    private const string ENDPOINT = 'endpoint';
    private const string REGION = 'us-west-1';
    private const string USERNAME = 'username';
    private const string TOKEN = 'token';
    private const int TTL = 100;

    private int $count = 0;

    /**
     * @return array{
     *     driver: Driver&MockObject,
     *     iam: IamDecoratorDoctrine30&MockObject,
     * }
     */
    public function testSuccessfullyConnected(): array
    {
        if (!$this->isDoctrine30()) {
            $this->markTestSkipped('Test is not compatible with Doctrine <3.0');
        }
        $params = [
            'host' => self::ENDPOINT,
            'port' => 5432,
            'user' => self::USERNAME,
        ];
        $driverMock = $this->createMock(Driver::class);
        $connectionMock = $this->createMock(Connection::class);

        $driverMock->expects(self::once())
            ->method('connect')
            ->with(
                array_merge(
                    $params,
                    ['password' => self::TOKEN],
                ),
            )->willReturn($connectionMock);

        $cacheStorageMock = $this->createMock(
            CacheStorageInterface::class,
        );
        $cacheStorageMock->expects(self::once())
            ->method('get')
            ->willReturn(null);

        $authMethodMock = $this->createMock(AuthTokenGenerator::class);
        $authMethodMock->expects(self::once())
            ->method('createToken')
            ->with(
                sprintf("%s:%d", self::ENDPOINT, 5432),
                self::REGION,
                self::USERNAME,
                self::TTL,
            )
            ->willReturn(self::TOKEN);

        $tokenProvider = new RdsTokenProvider($authMethodMock, self::TTL);

        $tokenProvider = new RdsTokenProviderCacheDecorator(
            $tokenProvider,
            $cacheStorageMock,
            self::TTL,
        );

        $decorator = new IamDecoratorDoctrine30(
            $driverMock,
            $tokenProvider,
            self::REGION,
        );

        $decorator->connect($params);

        return [
            'driver' => $driverMock,
            'iam' => $decorator,
        ];
    }

    public function testSuccessfullyReConnected(): void
    {
        if (!$this->isDoctrine30()) {
            $this->markTestSkipped('Test is not compatible with Doctrine <3.0');
        }

        $params = [
            'host' => self::ENDPOINT,
            'port' => 5432,
            'user' => self::USERNAME,
        ];
        $driverMock = $this->createMock(Driver::class);
        $connectionMock = $this->createMock(Connection::class);

        $count = 0;
        $driverMock->expects(self::exactly(2))
            ->method('connect')
            ->with(
                array_merge(
                    $params,
                    ['password' => self::TOKEN],
                ),
            )->willReturnCallback(function () use ($connectionMock): Connection {
                $this->count++;

                if (1 === $this->count) {
                    throw new DriverException(
                        new ConnectionLost(
                            Driver\OCI8\Exception\NonTerminatedStringLiteral::new(1),
                            null,
                        ),
                        new Query('', [], []),
                    );
                }

                return $connectionMock;
            });
        $exceptionConverter = $this->createMock(ExceptionConverter::class);
        $exceptionConverter->expects(self::once())
            ->method('convert')
            ->willReturn(
                new ConnectionLost(
                    Driver\OCI8\Exception\NonTerminatedStringLiteral::new(1),
                    null,
                ),
            );
        $driverMock->expects(self::once())
            ->method('getExceptionConverter')
            ->willReturn($exceptionConverter);

        $cacheStorageMock = $this->createMock(
            CacheStorageInterface::class,
        );
        $cacheStorageMock->expects(self::exactly(2))
            ->method('get')
            ->willReturn(null);

        $authMethodMock = $this->createMock(
            AuthTokenGenerator::class,
        );
        $authMethodMock->expects(self::exactly(2))
            ->method('createToken')
            ->with(
                sprintf("%s:%d", self::ENDPOINT, 5432),
                self::REGION,
                self::USERNAME,
                self::TTL,
            )
            ->willReturn(self::TOKEN);

        $tokenProvider = new RdsTokenProvider($authMethodMock, self::TTL);

        $tokenProvider = new RdsTokenProviderCacheDecorator(
            $tokenProvider,
            $cacheStorageMock,
            self::TTL,
        );

        $decorator = new IamDecoratorDoctrine30(
            $driverMock,
            $tokenProvider,
            self::REGION,
        );

        $decorator->connect($params);
    }

    public function testErrorException(): void
    {
        if (!$this->isDoctrine30()) {
            $this->markTestSkipped('Test is not compatible with Doctrine <3.0');
        }

        $params = [
            'host' => self::ENDPOINT,
            'port' => 5432,
            'user' => self::USERNAME,
        ];
        $driverMock = $this->createMock(Driver::class);

        $driverMock->expects(self::once())
            ->method('connect')
            ->with(
                array_merge(
                    $params,
                    ['password' => self::TOKEN],
                ),
            )->willReturnCallback(function (): Connection {
                throw new InvalidArgumentException();
            });

        $cacheStorageMock = $this->createMock(
            CacheStorageInterface::class,
        );
        $cacheStorageMock->expects(self::once())
            ->method('get')
            ->willReturn(null);

        $authMethodMock = $this->createMock(
            AuthTokenGenerator::class,
        );
        $authMethodMock->expects(self::once())
            ->method('createToken')
            ->with(
                sprintf("%s:%d", self::ENDPOINT, 5432),
                self::REGION,
                self::USERNAME,
                self::TTL,
            )
            ->willReturn(self::TOKEN);

        $tokenProvider = new RdsTokenProvider($authMethodMock, self::TTL);

        $tokenProvider = new RdsTokenProviderCacheDecorator(
            $tokenProvider,
            $cacheStorageMock,
            self::TTL,
        );

        $decorator = new IamDecoratorDoctrine30(
            $driverMock,
            $tokenProvider,
            self::REGION,
        );

        $this->expectException(InvalidArgumentException::class);
        $decorator->connect($params);
    }

    /**
     * @param array{
     *      driver: Driver&MockObject,
     *      iam: IamDecoratorDoctrine30&MockObject,
     *  } $data
     */
    #[Depends(methodName: 'testSuccessfullyConnected')]
    public function testGetDatabasePlatform(array $data): void
    {
        if (!$this->isDoctrine30()) {
            $this->markTestSkipped('Test is not compatible with Doctrine <3.0');
        }

        /** @var Driver&MockObject $driver */
        $driver = $data['driver'];
        /** @var IamDecoratorDoctrine30&MockObject $iam */
        $iam = $data['iam'];
        $platform = $this->createMock(AbstractPlatform::class);
        $driver->expects(self::once())
            ->method('getDatabasePlatform')
            ->willReturn($platform);

        self::assertEquals(
            $platform,
            $iam->getDatabasePlatform(
                $this->createMock(ServerVersionProvider::class),
            ),
        );
    }

    /**
     * @param array{
     *      driver: Driver&MockObject,
     *      iam: IamDecoratorDoctrine30&MockObject,
     *  } $data
     */
    #[Depends(methodName: 'testSuccessfullyConnected')]
    public function testGetExceptionConverter(array $data): void
    {
        if (!$this->isDoctrine30()) {
            $this->markTestSkipped('Test is not compatible with Doctrine <3.0');
        }

        /** @var Driver&MockObject $driver */
        $driver = $data['driver'];
        /** @var IamDecoratorDoctrine30&MockObject $iam */
        $iam = $data['iam'];
        $exceptionConverter = $this->createMock(ExceptionConverter::class);
        $driver->expects(self::once())
            ->method('getExceptionConverter')
            ->willReturn($exceptionConverter);

        self::assertEquals($exceptionConverter, $iam->getExceptionConverter());
    }
}
