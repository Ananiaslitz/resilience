<?php

namespace Resilience\RateLimiter;

use Closure;
use Resilience\Core\Contracts\ResilienceStrategy;
use Resilience\RateLimiter\Exceptions\RateLimitExceededException;
use Resilience\RateLimiter\Storage\RateLimiterStateManager;
use RuntimeException;

class RateLimiter implements ResilienceStrategy
{
    private ?Closure $onReject;

    public function __construct(
        private readonly string $name,
        private readonly RateLimiterStateManager $storage,
        private readonly int $limit = 10,
        private readonly int $periodSeconds = 1,
        ?callable $onReject = null
    ) {
        $this->onReject = $onReject ? $onReject(...) : null;
    }

    public function run(callable $callback): mixed
    {
        $now = microtime(true);
        $threshold = $now - $this->periodSeconds;

        $this->storage->cleanOldTimestamps($this->name, $threshold);
        $currentCount = $this->storage->countTimestamps($this->name);

        if ($currentCount >= $this->limit) {
            ($this->onReject) && ($this->onReject)($this->name);
            throw new RateLimitExceededException("Rate limit exceeded for [{$this->name}]");
        }

        $this->storage->appendTimestamp($this->name, $now);
        return $callback();
    }
}
