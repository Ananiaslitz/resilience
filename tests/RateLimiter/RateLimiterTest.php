<?php

namespace Resilience\Tests\RateLimiter;

use PHPUnit\Framework\TestCase;
use Resilience\RateLimiter\RateLimiter;
use Resilience\RateLimiter\Storage\RateLimiterStateManager;
use Resilience\RateLimiter\Exceptions\RateLimitExceededException;
use Mockery;

class RateLimiterTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testShouldAllowExecutionWhenBelowLimit(): void
    {
        $storage = Mockery::mock(RateLimiterStateManager::class);
        $storage->shouldReceive('cleanOldTimestamps')->once();
        $storage->shouldReceive('countTimestamps')->once()->andReturn(2);
        $storage->shouldReceive('appendTimestamp')->once();

        $limiter = new RateLimiter(
            name: 'test_limit',
            storage: $storage,
            limit: 5,
            periodSeconds: 10
        );

        $result = $limiter->run(fn () => 'ok');
        $this->assertEquals('ok', $result);
    }

    public function testShouldRejectExecutionWhenLimitExceeded(): void
    {
        $storage = Mockery::mock(RateLimiterStateManager::class);
        $storage->shouldReceive('cleanOldTimestamps')->once();
        $storage->shouldReceive('countTimestamps')->once()->andReturn(10);

        $limiter = new RateLimiter(
            name: 'test_limit',
            storage: $storage,
            limit: 10,
            periodSeconds: 10
        );

        $this->expectException(RateLimitExceededException::class);

        $limiter->run(fn () => 'should not happen');
    }
}
