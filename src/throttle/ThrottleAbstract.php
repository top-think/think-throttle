<?php
declare(strict_types=1);

namespace think\middleware\throttle;


use Psr\SimpleCache\CacheInterface;

abstract class ThrottleAbstract
{
    /** @var int */
    protected $cur_requests = 0;    // 当前已有的请求数
    /** @var int */
    protected $wait_seconds = 0;    // 距离下次合法请求还有多少秒

    /**
     * 是否允许访问
     * @param string $key           缓存键
     * @param float $micronow       当前时间戳,可含毫秒
     * @param int $max_requests     允许最大请求数
     * @param int $duration         限流时长
     * @param CacheInterface $cache 缓存对象
     * @return bool
     */
    abstract public function allowRequest(string $key, float $micronow, int $max_requests, int $duration, CacheInterface $cache): bool;

    /**
     * 计算距离下次合法请求还有多少秒
     * @return int
     */
    public function getWaitSeconds(): int
    {
        return (int) $this->wait_seconds;
    }

    /**
     * 当前已有的请求数
     * @return int
     */
    public function getCurRequests(): int
    {
        return (int) $this->cur_requests;
    }

}