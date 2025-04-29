<?php

namespace Resilience\Retry;

use Exception;
use Resilience\Core\Contracts\ResilienceStrategy;

class Retry implements ResilienceStrategy
{
    public function __construct(
        private int $maxAttempts = 3,
        private int $delayMs = 100,
    ) {}

    public function run(callable $callback): mixed
    {
        $attempt = 0;
        do {
            try {
                return $callback();
            } catch (Exception $e) {
                $attempt++;
                if ($attempt >= $this->maxAttempts) {
                    throw $e;
                }
                usleep($this->delayMs * 1000);
            }
        } while (true);
    }
}
