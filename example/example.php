<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Resilience\CircuitBreaker\CircuitBreaker;
use Resilience\CircuitBreaker\Listeners\CircuitBreakerListener;
use Resilience\CircuitBreaker\Storage\InMemoryStorage;

$breaker = new CircuitBreaker(
    name: 'external_api',
    storage: new InMemoryStorage(),
    failureThreshold: 3,
    openTimeout: 5,
    listener: new class implements CircuitBreakerListener {
        public function onOpen(string $name): void { echo "[$name] ➡️ OPEN\n"; }
        public function onClose(string $name): void { echo "[$name] ✅ CLOSED\n"; }
        public function onHalfOpen(string $name): void { echo "[$name] 🔄 HALF_OPEN\n"; }
    }
);

for ($i = 1; $i <= 10; $i++) {
    echo "\n🔁 Tentativa #$i\n";

    try {
        $result = $breaker->run(fn() => someApiCall());
        echo "➡️ Resultado: $result\n";
    } catch (Exception $e) {
        echo "⚠️ Exceção capturada: {$e->getMessage()}\n";
    }

    sleep(1);
}


function someApiCall(): string
{
    echo "🟢 Chamando API...\n";

    sleep(1);

    if (0 === 0) {
        echo "🔴 Falhou!\n";
        throw new Exception("Erro na API");
    }

    echo "✅ Sucesso!\n";
    return "dados da API";
}
