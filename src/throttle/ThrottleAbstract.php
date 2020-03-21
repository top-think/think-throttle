<?php


namespace think\middleware\throttle;


abstract class ThrottleAbstract
{
    protected $cur_requests = 0;    // 当前已有的请求数
    protected $wait_seconds = 0;    // 距离下次合法请求还有多少秒

    /**
     * 是否允许访问
     * @param string $key       缓存键
     * @param int $now          当前时间戳
     * @param int $max_requests 允许最大请求数
     * @param int $duration     限流时长
     * @param $cache            缓存对象
     * @return mixed
     */
    abstract public function allowRequest(string $key, int $now, int $max_requests, int $duration, $cache);

    /**
     * 计算距离下次合法请求还有多少秒
     * @return int
     */
    public function getWaitSeconds() {
        return $this->wait_seconds;
    }

    /**
     * 当前已有的请求数
     * @return int
     */
    public function getCurRequests() {
        return $this->cur_requests;
    }

    /**
     * 完成请求时的回调
     */
    public function finishRequest($key, $cache) {}

    /**
     * 拒绝请求时回调
     */
    public function denyRequest($key, $cache) {}

}