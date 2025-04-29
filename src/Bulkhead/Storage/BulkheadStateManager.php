<?php

namespace Resilience\Bulkhead\Storage;

interface BulkheadStateManager
{
    public function acquire(): bool;

    public function release(): void;
}
