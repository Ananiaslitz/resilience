<?php

namespace Resilience\RateLimiter\Storage;

interface RateLimiterStateManager
{
    /**
     * Appends a timestamp associated with a given key (bucket).
     *
     * @param string $key       The identifier for the rate limiter bucket.
     * @param float  $timestamp The timestamp to record (in microseconds).
     */
    public function appendTimestamp(string $key, float $timestamp): void;

    /**
     * Removes all timestamps older than the given threshold for the specified key.
     *
     * @param string $key       The identifier for the rate limiter bucket.
     * @param float  $threshold The cutoff timestamp; entries older than this will be removed.
     */
    public function cleanOldTimestamps(string $key, float $threshold): void;

    /**
     * Returns the number of timestamps currently stored for the given key.
     *
     * @param string $key The identifier for the rate limiter bucket.
     * @return int         The number of stored timestamps.
     */
    public function countTimestamps(string $key): int;
}
