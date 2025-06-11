<?php

namespace Resilience\Retry;

use Exception;
use Resilience\Core\Contracts\ResilienceStrategy;
use Throwable;

class Retry implements ResilienceStrategy
{
    private $shouldRetry;
    private $onRetry;
    private $backoff;

    /**
     * @param int $maxAttempts Número máximo de tentativas
     * @param callable|null $shouldRetry Função que recebe o Throwable e retorna bool
     * @param callable|null $onRetry Callback em cada falha (Throwable $e, int $attempt)
     * @param callable|null $backoff Estratégia de tempo entre tentativas (recebe attempt, retorna delay em ms)
     */
    public function __construct(
        private int $maxAttempts = 3,
        ?callable $shouldRetry = null,
        ?callable $onRetry = null,
        ?callable $backoff = null,
    ) {}

    public function run(callable $callback): mixed
    {
        $attempt = 0;
        while (true) {
            try {
                return $callback();
            } catch (Throwable $e) {
                $attempt++;

                if ($this->onRetry) {
                    ($this->onRetry)($e, $attempt);
                }

                $shouldRetry = $this->shouldRetry ? ($this->shouldRetry)($e) : true;

                if (!$shouldRetry || $attempt >= $this->maxAttempts) {
                    throw $e;
                }

                $delay = $this->backoff ? ($this->backoff)($attempt) : 100;
                usleep($delay * 1000);
            }
        }
    }
}