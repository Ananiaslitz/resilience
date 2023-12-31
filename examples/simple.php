<?php

require_once './vendor/autoload.php';

use Resilience\Retry;

$operation = function() {
    if (rand(0, 1) === 0) {
        throw new Exception("Operation failed");
    }
    return "Operation successful";
};

$maxAttempts = 1;
$initialDelay = 1000;

try {
    $result = Retry::executeWithBackoff($operation, $maxAttempts, $initialDelay);
    echo $result . "\n";
} catch (Exception $e) {
    echo "All attempts failed: " . $e->getMessage() . "\n";
}
