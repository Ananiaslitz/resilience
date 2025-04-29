<?php

namespace Resilience\Tests\RateLimiter\Storage;

use PHPUnit\Framework\TestCase;
use Resilience\RateLimiter\Storage\InMemoryRateLimiterStateManager;

class InMemoryRateLimiterStateManagerTest extends TestCase
{
    public function testAppendAndCountTimestamps(): void
    {
        $manager = new InMemoryRateLimiterStateManager();
        $manager->appendTimestamp('key1', 100.0);
        $manager->appendTimestamp('key1', 101.0);

        $this->assertEquals(2, $manager->countTimestamps('key1'));
    }

    public function testCleanOldTimestamps(): void
    {
        $manager = new InMemoryRateLimiterStateManager();
        $manager->appendTimestamp('key1', 95.0);
        $manager->appendTimestamp('key1', 100.0);
        $manager->appendTimestamp('key1', 105.0);

        $manager->cleanOldTimestamps('key1', 100.0);

        $this->assertEquals(2, $manager->countTimestamps('key1'));
    }

    public function testCountOnEmptyKeyReturnsZero(): void
    {
        $manager = new InMemoryRateLimiterStateManager();

        $this->assertEquals(0, $manager->countTimestamps('nonexistent_key'));
    }
}
