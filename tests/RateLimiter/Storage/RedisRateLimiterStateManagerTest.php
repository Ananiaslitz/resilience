<?php

namespace Resilience\Tests\RateLimiter\Storage;

use Mockery;
use PHPUnit\Framework\TestCase;
use Resilience\RateLimiter\Storage\RedisRateLimiterStateManager;
use Predis\Client;

class RedisRateLimiterStateManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testAppendTimestamp()
    {
        $key = 'ratelimit:test';
        $timestamp = microtime(true);
        $called = false;

        $mock = Mockery::mock(Client::class);
        $mock->shouldReceive('zadd')
            ->once()
            ->with($key, [$timestamp => $timestamp])
            ->andReturnUsing(function () use (&$called) {
                $called = true;
                return 1;
            });

        $mock->shouldReceive('expire')
            ->once()
            ->with($key, 60);

        $manager = new RedisRateLimiterStateManager($mock);
        $manager->appendTimestamp($key, $timestamp);

        $this->assertTrue($called);
    }

    public function testCleanOldTimestamps()
    {
        $redis = Mockery::mock(Client::class);
        $manager = new RedisRateLimiterStateManager($redis);

        $key = 'ratelimit:test';
        $threshold = microtime(true) - 10;

        $redis->shouldReceive('zremrangebyscore')
            ->once()
            ->with($key, 0, $threshold);

        $manager->cleanOldTimestamps($key, $threshold);

        $this->assertTrue(true);
    }


    public function testCountTimestamps()
    {
        $redis = Mockery::mock(Client::class);
        $manager = new RedisRateLimiterStateManager($redis);

        $key = 'ratelimit:test';

        $redis->shouldReceive('zcard')
            ->once()
            ->with($key)
            ->andReturn(5);

        $this->assertSame(5, $manager->countTimestamps($key));
    }
}
