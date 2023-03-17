<?php

namespace think\middleware\throttle;


use think\Cache;

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
     * @param Cache $cache 缓存对象
     * @return bool
     */
    abstract public function allowRequest($key, $micronow, $max_requests, $duration, Cache $cache);

    /**
     * 计算距离下次合法请求还有多少秒
     * @return int
     */
    public function getWaitSeconds()
    {
        return (int) $this->wait_seconds;
    }

    /**
     * 当前已有的请求数
     * @return int
     */
    public function getCurRequests()
    {
        return (int) $this->cur_requests;
    }

}