<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Aws\Token;

use Macpaw\DoctrineAwsIamRdsAuthBundle\Cache\CacheStorageInterface;

final readonly class RdsTokenProviderCacheDecorator implements TokenProviderInterface
{
    private const string CACHE_KEY_PREFIX = 'rds_token_';

    public function __construct(
        private TokenProviderInterface $tokenProvider,
        private CacheStorageInterface $cacheStorage,
        private int $ttl
    ) {
    }

    public function getToken(string $endpoint, string $region, string $username, bool $refresh = false): string
    {
        $key = $this->getCacheKey($endpoint, $region, $username);
        $token = $this->cacheStorage->get($key);

        if ($refresh || $token === false || !is_string($token)) {
            $token = $this->tokenProvider->getToken($endpoint, $region, $username);
            $this->cacheStorage->set($key, $token, $this->ttl);
        }

        return $token;
    }

    private function getCacheKey(string $endpoint, string $region, string $username): string
    {
        return self::CACHE_KEY_PREFIX . urlencode("{$endpoint}_{$region}_{$username}");
    }
}
