<?php

namespace Resilience\Core\Contracts;

interface ResilienceStrategy
{
    public function run(callable $callback): mixed;
}