<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use Resilience\RateLimiter\RateLimiter;
use Resilience\RateLimiter\Exceptions\RateLimitExceededException;
use Resilience\RateLimiter\Storage\InMemoryRateLimiterStateManager;

$rateLimiter = new RateLimiter(
    name: 'api',
    storage: new InMemoryRateLimiterStateManager(),
    limit: 3,
    periodSeconds: 5,
    onReject: fn(string $name) => print "[REJECTED] Bucket $name limit reached!\n"
);

for ($i = 0; $i < 5; $i++) {
    try {
        $rateLimiter->run(fn() => print "✅ Request $i accepted\n");
    } catch (RateLimitExceededException $e) {
        print "❌ Request $i rejected: {$e->getMessage()}\n";
    }

    usleep(500_000);
}
