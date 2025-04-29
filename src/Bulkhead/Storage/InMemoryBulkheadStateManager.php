<?php

namespace Resilience\Bulkhead\Storage;

class InMemoryBulkheadStateManager implements BulkheadStateManager
{
    private int $concurrent = 0;

    public function __construct(private int $limit = 2) {}

    public function acquire(): bool
    {
        if ($this->concurrent >= $this->limit) {
            return false;
        }

        $this->concurrent++;
        return true;
    }

    public function release(): void
    {
        $this->concurrent--;
    }
}
