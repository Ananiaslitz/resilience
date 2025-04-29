<?php

namespace Resilience\Bulkhead;

use Resilience\Bulkhead\Storage\BulkheadStateManager;
use Resilience\Core\Contracts\ResilienceStrategy;
use RuntimeException;

class Bulkhead implements ResilienceStrategy
{
    public function __construct(
        private BulkheadStateManager $stateManager,
        private string $name = 'default',
        private int $acquireTimeoutMs = 0,
        private ?BulkheadListener $listener = null
    ) {}

    public function run(callable $callback): mixed
    {
        $start = microtime(true);
        $acquired = false;

        do {
            $acquired = $this->stateManager->acquire();

            if ($acquired) {
                $this->listener?->onEnter($this->name);
                break;
            }

            if ($this->acquireTimeoutMs === 0) {
                break;
            }

            usleep(1000); // espera 1ms
        } while ((microtime(true) - $start) < ($this->acquireTimeoutMs / 1000));

        if (!$acquired) {
            $this->listener?->onReject($this->name);
            throw new RuntimeException("Bulkhead limit reached (timeout)");
        }

        try {
            return $callback();
        } finally {
            $this->stateManager->release();
            $this->listener?->onRelease($this->name);
        }
    }
}
