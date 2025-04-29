<?php

namespace Resilience\Bulkhead;

interface BulkheadListener
{
    public function onEnter(string $name): void;

    public function onReject(string $name): void;

    public function onRelease(string $name): void;
}
