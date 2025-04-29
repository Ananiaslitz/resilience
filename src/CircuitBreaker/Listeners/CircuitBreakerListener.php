<?php

namespace Resilience\CircuitBreaker\Listeners;

interface CircuitBreakerListener {
    public function onOpen(string $name): void;
    public function onClose(string $name): void;
    public function onHalfOpen(string $name): void;
}