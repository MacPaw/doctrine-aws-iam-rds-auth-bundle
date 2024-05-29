<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Cache;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;

final readonly class CacheStorage implements CacheStorageInterface
{
    public function __construct(private AdapterInterface $cacheAdapter)
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function set(string $key, mixed $value, int $ttl = 0): void
    {
        $cacheItem = $this->cacheAdapter->getItem($key);
        $cacheItem->set($value);

        if ($ttl > 0) {
            $cacheItem->expiresAfter($ttl);
        }

        $this->cacheAdapter->save($cacheItem);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $key): mixed
    {
        $cacheItem = $this->cacheAdapter->getItem($key);
        if (!$cacheItem->isHit()) {
            return false;
        }

        return $cacheItem->get();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(string $key): void
    {
        $this->cacheAdapter->deleteItem($key);
    }
}
