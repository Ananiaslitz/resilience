<?php

namespace Resilience\CircuitBreaker\Storage;

interface CircuitBreakerStorage
{
    public function getState(string $name): string;

    public function recordSuccess(string $name): void;

    public function recordFailure(string $name): void;

    public function isThresholdExceeded(string $name, int $failureThreshold): bool;

    public function open(string $name, int $openTimeout): void;

    public function canAttemptHalfOpen(string $name, int $openTimeout): bool;
}
