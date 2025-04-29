<?php

namespace Resilience\Core;

use Resilience\Core\Contracts\ResilienceStrategy;

class Resilient
{
    private array $strategies = [];

    public static function create(): self
    {
        return new self();
    }

    public function withStrategy(ResilienceStrategy $strategy): self
    {
        $this->strategies[] = $strategy;
        return $this;
    }

    public function run(callable $callback): mixed
    {
        $composed = array_reduce(
            array_reverse($this->strategies),
            fn ($carry, ResilienceStrategy $strategy) => fn () => $strategy->run($carry),
            $callback
        );

        return $composed();
    }
}
