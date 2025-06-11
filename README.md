# 🛡️ PHP Resilience

Uma biblioteca de **resiliência para aplicações PHP**, inspirada em padrões como *Resilience4j*, projetada para ser leve, extensível e compatível com qualquer framework.

> 📌 Desenvolvido com foco em **alta disponibilidade**, **isolamento de falhas** e **escalabilidade controlada**.

---

## ✨ Padrões Suportados

- ✅ **Circuit Breaker** – Abre, fecha e meio-abre com base em falhas controladas.
- ✅ **Rate Limiter** – Controla a taxa de chamadas por chave (InMemory ou Redis).
- ✅ **Bulkhead** – Isola blocos de execução com limite de concorrência simultânea.
- 🔜 **Retry** – Tentativas automáticas com controle de backoff.
- 🔜 **Time Limiter** – Cancela execuções que excedem o tempo permitido.

---

## 🚀 Instalação

```bash
composer require sualib/php-resilience
```

Requer PHP >= 8.1


## 🧩 Exemplo de Uso
### Circuit Breaker

```php
$circuitBreaker = new InMemoryCircuitBreaker('service-a');

try {
    $circuitBreaker->call(function () {
        // chamada instável aqui
    });
} catch (CircuitBreakerOpenException $e) {
    // fallback
}
```
### Rate Limiter (Redis)
```php
$redis = new Redis(); // configure sua conexão
$stateManager = new RedisRateLimiterStateManager($redis);

$rateLimiter = new RateLimiter(
    'api-user-123',
    10, // 10 requisições
    60, // por 60 segundos
    $stateManager
);

if (!$rateLimiter->tryConsume()) {
    throw new TooManyRequestsException();
}
```

### Bulkhead
```php
$bulkhead = new InMemoryBulkhead('email-sender', 5);

try {
    $bulkhead->call(fn() => enviarEmail());
} catch (BulkheadFullException $e) {
    // fallback ou fila
}
```

## Testes
Utilize o PHPUnit com Mockery:
```bash 
composer test
```

## Arquitetura
- Totalmente orientado a contratos (interfaces).

- Estado externo (ex: Redis) separado por StateManager.

- Sem dependência de framework.

- Pronto para uso em microserviços, APIs REST ou workers assíncronos.

### 🗺️ Roadmap
✅ Circuit Breaker (InMemory e Redis)

✅ Rate Limiter (InMemory e Redis)

✅ Bulkhead (com timeout ou fila opcional)

✅ Retry com controle de tentativas

✅ TimeLimiter

⬜ Suporte ao Symfony/PSR para middlewares plugáveis

⬜ Observabilidade (handlers de métricas e eventos)

