<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Tests\Unit;

use Aws\Rds\AuthTokenGenerator;
use InvalidArgumentException;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Aws\Token\RdsTokenProvider;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Aws\Token\RdsTokenProviderCacheDecorator;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Cache\CacheStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class CacheRdsTokenProviderTest extends TestCase
{
    private const string ENDPOINT = 'endpoint';
    private const string REGION = 'us-west-1';
    private const string USERNAME = 'username';
    private const string TOKEN = 'token';
    private const int TTL = 100;

    public function testSuccessfullyTokenGenerated(): void
    {
        $mock = $this->createMock(AuthTokenGenerator::class);
        $mock->expects(self::once())
            ->method('createToken')
            ->with(
                self::ENDPOINT,
                self::REGION,
                self::USERNAME,
                self::TTL,
            )
            ->willReturn('token');

        $provider = $this->getCacheProvider($mock);

        $token = $provider->getToken(
            self::ENDPOINT,
            self::REGION,
            self::USERNAME,
        );

        self::assertEquals(self::TOKEN, $token);

        $token = $provider->getToken(
            self::ENDPOINT,
            self::REGION,
            self::USERNAME,
        );

        self::assertEquals(self::TOKEN, $token);
    }

    public function testInvalidTTL(): void
    {
        $mock = $this->createMock(AuthTokenGenerator::class);
        $mock->expects(self::once())
            ->method('createToken')
            ->with(
                self::ENDPOINT,
                self::REGION,
                self::USERNAME,
                -1,
            )
            ->willThrowException(new InvalidArgumentException());

        $provider = $this->getProvider($mock, -1);

        self::expectException(InvalidArgumentException::class);
        $provider->getToken(
            self::ENDPOINT,
            self::REGION,
            self::USERNAME,
        );
    }

    private function getProvider(
        AuthTokenGenerator $generator,
        int $ttl = self::TTL,
    ): RdsTokenProvider {
        return new RdsTokenProvider($generator, $ttl);
    }

    private function getCacheProvider(
        AuthTokenGenerator $generator,
        int $ttl = self::TTL,
    ): RdsTokenProviderCacheDecorator
    {
        $adapter = new FilesystemAdapter('');
        $adapter->clear();

        return new RdsTokenProviderCacheDecorator(
            $this->getProvider($generator, $ttl),
            new CacheStorage($adapter),
            $ttl,
        );
    }
}
