<?php

namespace Resilience\TimeLimiter;

use Resilience\Core\Contracts\ResilienceStrategy;
use Resilience\TimeLimiter\Exceptions\TimeLimiterException;
use RuntimeException;

class TimeLimiter implements ResilienceStrategy
{
    private $fallback;

    public function __construct(
        private int $timeoutSeconds = 2,
        ?callable $fallback = null
    ) {}

    public function run(callable $callback): mixed
    {
        if (!function_exists('pcntl_alarm') || php_sapi_name() !== 'cli') {
            return $callback();
        }

        pcntl_async_signals(true);
        $previousHandler = pcntl_signal_get_handler(SIGALRM);

        pcntl_signal(SIGALRM, function () {
            throw new TimeLimiterException("Execution time exceeded {$this->timeoutSeconds} seconds");
        });

        pcntl_alarm($this->timeoutSeconds);

        try {
            return $callback();
        } catch (TimeLimiterException $e) {
            return $this->fallback ? call_user_func($this->fallback) : throw $e;
        } finally {
            pcntl_alarm(0);
            if (is_callable($previousHandler)) {
                pcntl_signal(SIGALRM, $previousHandler);
            }
        }
    }
}
