<?php

namespace Resilience\TimeLimiter;

use Resilience\Core\Contracts\ResilienceStrategy;
use RuntimeException;

class TimeLimiter implements ResilienceStrategy
{
    public function __construct(private int $timeoutSeconds = 2) {}

    public function run(callable $callback): mixed
    {
        if (!function_exists('pcntl_alarm')) {
            return $callback(); // fallback sem timeout
        }

        pcntl_async_signals(true);
        pcntl_signal(SIGALRM, function () {
            throw new RuntimeException("Time limit exceeded");
        });

        pcntl_alarm($this->timeoutSeconds);
        try {
            return $callback();
        } finally {
            pcntl_alarm(0);
        }
    }
}
