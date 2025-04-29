<?php

namespace Resilience\RateLimiter\Storage;

use Predis\Client;

class RedisRateLimiterStateManager implements RateLimiterStateManager
{
    public function __construct(private Client $redis) {}

    public function cleanOldTimestamps(string $key, float $threshold): void
    {
        $this->redis->zremrangebyscore($key, 0, $threshold);
    }

    public function countTimestamps(string $key): int
    {
        return $this->redis->zcard($key);
    }

    public function appendTimestamp(string $key, float $timestamp): void
    {
        $this->redis->zadd($key, [$timestamp => $timestamp]);
        $this->redis->expire($key, 60); // evitar vazamento de memória
    }
}
