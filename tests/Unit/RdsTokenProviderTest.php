<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Tests\Unit;

use Aws\Rds\AuthTokenGenerator;
use InvalidArgumentException;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Aws\Token\RdsTokenProvider;
use PHPUnit\Framework\TestCase;

final class RdsTokenProviderTest extends TestCase
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
            ->willReturn(self::TOKEN);

        $provider = $this->getProvider($mock);

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
}
