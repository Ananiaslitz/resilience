<?php

namespace Resilience\Tests\CircuitBreaker;

use Exception;
use PHPUnit\Framework\TestCase;
use Resilience\CircuitBreaker\CircuitBreaker;
use Resilience\CircuitBreaker\Storage\InMemoryStorage;
use RuntimeException;

class CircuitBreakerTest extends TestCase
{
    public function testBreakerOpensAfterFailures()
    {
        $storage = new InMemoryStorage();
        $cb = new CircuitBreaker('api_x', $storage, failureThreshold: 2, openTimeout: 5);

        $this->expectException(Exception::class);
        $cb->run(fn () => throw new Exception("fail 1"));

        $this->expectException(Exception::class);
        $cb->run(fn () => throw new Exception("fail 2"));

        $this->expectException(RuntimeException::class);
        $cb->run(fn () => "never gets here");
    }
}
