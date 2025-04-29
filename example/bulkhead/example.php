<?php

require_once "vendor/autoload.php";

use Predis\Client;
use Resilience\Bulkhead\Bulkhead;
use Resilience\Bulkhead\Storage\RedisBulkheadStateManager;
use Resilience\Bulkhead\BulkheadListener;

$redis = new Client(['host' => 'redis', 'port' => 6379]);

$bulkhead = new Bulkhead(
    stateManager: new RedisBulkheadStateManager($redis, 'stress_test', 3, 10),
    name: 'stress_test',
    acquireTimeoutMs: 2000,
    listener: new class implements BulkheadListener {
        public function onEnter(string $name): void   { echo "[ENTER]    $name\n"; }
        public function onReject(string $name): void  { echo "[REJECTED] $name\n"; }
        public function onRelease(string $name): void { echo "[RELEASE]  $name\n"; }
    }
);

$processCount = 10;

for ($i = 0; $i < $processCount; $i++) {
    $pid = pcntl_fork();

    if ($pid === 0) {
        try {
            $bulkhead->run(function () use ($i) {
                echo ">> Processo #$i executando...\n";
                $duration = rand(1, 3);
                sleep($duration);

                if (rand(0, 4) === 0) {
                    throw new Exception("Simulação de falha no processo #$i");
                }

                echo "✔️ Processo #$i finalizado com sucesso!\n";
            });
        } catch (Exception $e) {
            echo "❌ Processo #$i falhou: {$e->getMessage()}\n";
        }

        exit(0);
    }
}

while (pcntl_wait($status) > 0);
