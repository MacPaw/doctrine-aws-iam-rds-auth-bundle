<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Tests\Functional\Container;

use Aws\Rds\AuthTokenGenerator;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Aws\Token\RdsTokenProvider;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Aws\Token\RdsTokenProviderCacheDecorator;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Cache\CacheStorageInterface;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Doctrine\Driver\IamMiddleware;
use Macpaw\DoctrineAwsIamRdsAuthBundle\Tests\Functional\AbstractFunctional;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class DIExtensionTest extends AbstractFunctional
{
    public function testRdsTokenProviderInit(): void
    {
        $provider = self::getContainer()->get(RdsTokenProvider::class);

        self::assertInstanceOf(
            RdsTokenProviderCacheDecorator::class,
            $provider,
        );

        $reflection = new ReflectionClass($provider);
        $prop = $reflection->getProperty('tokenProvider');
        $prop->setAccessible(true);

        $provider = $prop->getValue($provider);
        self::assertInstanceOf(RdsTokenProvider::class, $provider);

        $reflection = new ReflectionClass($provider);
        $prop = $reflection->getProperty('lifetime');
        $prop->setAccessible(true);

        self::assertEquals(
            10,
            $prop->getValue($provider),
        );

        $reflection = new ReflectionClass($provider);
        $prop = $reflection->getProperty('generator');
        $prop->setAccessible(true);

        self::assertInstanceOf(
            AuthTokenGenerator::class,
            $prop->getValue($provider),
        );
    }

    public function testIamDecorator(): void
    {
        $tokenProvider = self::getContainer()->get(RdsTokenProviderCacheDecorator::class);
        $iamDecorator = self::getContainer()->get(IamMiddleware::class);

        $reflection = new ReflectionClass($iamDecorator);
        $prop = $reflection->getProperty('tokenProvider');
        $prop->setAccessible(true);

        self::assertEquals(
            $tokenProvider,
            $prop->getValue($iamDecorator),
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $cacheDecorator = self::getContainer()->get(CacheStorageInterface::class);
        $reflection = new ReflectionClass($cacheDecorator);
        $prop = $reflection->getProperty('cacheAdapter');
        /** @var FilesystemAdapter $cacheAdapter */
        $cacheAdapter = $prop->getValue($cacheDecorator);
        $cacheAdapter->clear();

        putenv('APP_ENV=test');
    }
}
