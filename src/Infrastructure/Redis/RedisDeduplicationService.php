<?php

namespace App\Infrastructure\Redis;

use App\Infrastructure\Contract\DeduplicationServiceInterface;
use Redis;

class RedisDeduplicationService implements DeduplicationServiceInterface
{
    private const TTL = 600; // 10 минут

    public function __construct(
        private Redis $redis
    ) {}

    public function acquire(int $taskId): bool
    {
        $key = $this->buildKey($taskId);

        // SET key value NX EX ttl
        $result = $this->redis->set(
            $key,
            '1',
            [
                'nx',
                'ex' => self::TTL,
            ]
        );

        return $result === true;
    }

    public function release(int $taskId): void
    {
        $key = $this->buildKey($taskId);
        $this->redis->del($key);
    }

    private function buildKey(int $taskId): string
    {
        return "task:processed:{$taskId}";
    }
}
