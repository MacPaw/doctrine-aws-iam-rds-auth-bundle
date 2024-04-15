<?php

declare(strict_types=1);

namespace Macpaw\DoctrineAwsIamRdsAuthBundle\Cache;

interface CacheStorageInterface
{
    public function set(string $key, mixed $value, int $ttl = 0): void;

    public function get(string $key): mixed;

    public function delete(string $key): void;
}
