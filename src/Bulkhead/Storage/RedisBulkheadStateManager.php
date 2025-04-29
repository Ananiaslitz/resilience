<?php

namespace Resilience\Bulkhead\Storage;

use Predis\Client;

class RedisBulkheadStateManager implements BulkheadStateManager
{
    public function __construct(
        private Client $redis,
        private string $name,
        private int $limit = 2,
        private int $ttlSeconds = 10
    ) {}

    public function acquire(): bool
    {
        $key = $this->key();

        $responses = $this->redis->pipeline(function ($pipe) use ($key) {
            $pipe->incr($key);
            $pipe->expire($key, $this->ttlSeconds);
        });

        if (!is_array($responses)) {
            throw new \RuntimeException("Pipeline failed to return an array");
        }

        $count = $responses[0] ?? 0;

        if (!is_numeric($count)) {
            throw new \RuntimeException("Unexpected response from Redis for INCR");
        }

        if ($count > $this->limit) {
            $this->redis->decr($key); // desfaz o incremento
            return false;
        }

        return true;
    }

    public function release(): void
    {
        $this->redis->decr($this->key());
    }

    private function key(): string
    {
        return "bulkhead:{$this->name}";
    }
}
