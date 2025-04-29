<?php

namespace Resilience\CircuitBreaker\Storage;

class InMemoryStorage implements CircuitBreakerStorage
{
    private array $states = [];
    private array $failures = [];
    private array $openedAt = [];
    private array $stats = [];

    public function setState(string $name, string $state): void
    {
        $this->states[$name] = $state;
        $this->stats[$name]['lastStateChange'] = time();
    }

    public function getStats(string $name): array
    {
        return [
            'state' => $this->getState($name),
            'failures' => $this->failures[$name] ?? 0,
            'openedAt' => $this->openedAt[$name] ?? null,
            'lastStateChange' => $this->stats[$name]['lastStateChange'] ?? null
        ];
    }

    public function getState(string $name): string
    {
        return $this->states[$name] ?? 'CLOSED';
    }

    public function recordSuccess(string $name): void
    {
        $this->failures[$name] = 0;
        $this->states[$name] = 'CLOSED';
    }

    public function recordFailure(string $name): void
    {
        $this->failures[$name] = ($this->failures[$name] ?? 0) + 1;
    }

    public function isThresholdExceeded(string $name, int $failureThreshold): bool
    {
        return ($this->failures[$name] ?? 0) >= $failureThreshold;
    }

    public function open(string $name, int $openTimeout): void
    {
        $this->states[$name] = 'OPEN';
        $this->openedAt[$name] = time();
    }

    public function canAttemptHalfOpen(string $name, int $openTimeout): bool
    {
        if (!isset($this->openedAt[$name])) return true;
        return (time() - $this->openedAt[$name]) >= $openTimeout;
    }
}
