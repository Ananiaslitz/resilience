<?php

namespace Resilience\CircuitBreaker;

use Exception;
use Resilience\CircuitBreaker\Listeners\CircuitBreakerListener;
use RuntimeException;
use Resilience\CircuitBreaker\Storage\CircuitBreakerStorage;
use Resilience\Core\Contracts\ResilienceStrategy;

class CircuitBreaker implements ResilienceStrategy
{
    public const STATE_CLOSED = 'CLOSED';
    public const STATE_OPEN = 'OPEN';
    public const STATE_HALF_OPEN = 'HALF_OPEN';

    public function __construct(
        private readonly string $name,
        private readonly CircuitBreakerStorage $storage,
        private readonly int $failureThreshold = 3,
        private readonly int $openTimeout = 10,
        private readonly ?CircuitBreakerListener $listener = null,
        private readonly array $ignoredExceptions = []
    ) {}

    public function run(callable $callback, ?callable $fallback = null): mixed
    {
        $state = $this->storage->getState($this->name);

        if ($state === self::STATE_OPEN) {
            if (!$this->storage->canAttemptHalfOpen($this->name, $this->openTimeout)) {
                throw new RuntimeException("Circuit [$this->name] is OPEN");
            }

            $this->storage->setState($this->name, self::STATE_HALF_OPEN);
            $this->listener?->onHalfOpen($this->name);
            $state = self::STATE_HALF_OPEN;
        }

        try {
            $result = $callback();
            $this->storage->recordSuccess($this->name);
            $this->storage->setState($this->name, self::STATE_CLOSED);
            $this->listener?->onClose($this->name);
            return $result;
        } catch (Exception $e) {
            foreach ($this->ignoredExceptions as $ignored) {
                if ($e instanceof $ignored) {
                    throw $e;
                }
            }

            $this->storage->recordFailure($this->name);

            if ($this->storage->isThresholdExceeded($this->name, $this->failureThreshold)) {
                $this->storage->open($this->name, $this->openTimeout);
                $this->storage->setState($this->name, self::STATE_OPEN);
                $this->listener?->onOpen($this->name);
            }

            if ($fallback) {
                return $fallback($e);
            }

            throw $e;
        }
    }
}
