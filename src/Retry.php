<?php

namespace Resilience;

class Retry
{
    /**
     * @throws \Exception
     */
    public static function executeWithBackoff(callable $operation, int $maxAttempts, int $initialDelay = 1000, int $maxDelay = 10000)
    {
        $attempt = 0;
        while ($attempt < $maxAttempts) {
            try {
                return $operation();
            } catch (\Exception $e) {
                $attempt++;
                $delay = min(self::calculateDelay($initialDelay, $attempt), $maxDelay);
                usleep($delay * 1000);
            }
        }

        throw new \Exception("Max attempts reached");
    }

    private static function calculateDelay($initialDelay, $attempt): float|int
    {
        return $initialDelay * pow(2, $attempt - 1);
    }
}