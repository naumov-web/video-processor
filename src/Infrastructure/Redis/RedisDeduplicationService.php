<?php

namespace App\Infrastructure\Redis;

use Redis;

class RedisDeduplicationService
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

    private function buildKey(int $taskId): string
    {
        return "task:processed:{$taskId}";
    }
}
