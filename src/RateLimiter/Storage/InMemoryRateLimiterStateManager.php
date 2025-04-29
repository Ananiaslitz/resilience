<?php

namespace Resilience\RateLimiter\Storage;

class InMemoryRateLimiterStateManager implements RateLimiterStateManager
{
    private array $storage = [];

    public function appendTimestamp(string $key, float $timestamp): void
    {
        $this->storage[$key][] = $timestamp;
    }

    public function cleanOldTimestamps(string $key, float $threshold): void
    {
        $this->storage[$key] = array_filter(
            $this->storage[$key] ?? [],
            fn ($t) => $t >= $threshold
        );
    }

    public function countTimestamps(string $key): int
    {
        return count($this->storage[$key] ?? []);
    }
}
