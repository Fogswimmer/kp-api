<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpFoundation\RequestStack;

class VisitorCounterService
{
    private \Redis|\RedisCluster $redis;
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->redis = RedisAdapter::createConnection($_ENV['REDIS_URL']);
        $this->requestStack = $requestStack;
    }

    public function increment(): int
    {
        $request = $this->requestStack->getCurrentRequest();
        $ip = $request->getClientIp();

        $key = 'site:visits:unique:' . $ip;

        if (!$this->redis->exists($key)) {
            $this->redis->setex($key, 86400, 1);
            $this->redis->incr('site:visits:unique'); //
        }

        return (int) $this->redis->get('site:visits:unique');
    }
}
