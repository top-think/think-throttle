<?php


namespace think\middleware\throttle;

/**
 * 计数器固定窗口算法
 * Class CounterFixed
 * @package think\middleware\throttle
 */
class CounterFixed extends ThrottleAbstract
{

    public function allowRequest(string $key, int $now, int $max_requests, int $duration, $cache)
    {
        $cur_requests = $cache->get($key, 0);
        $limit_flag = $cache->get($key . 'flag', null);
        $wait_reset_seconds = $duration - $now % $duration;     // 距离下次重置还有n秒时间

        if ($limit_flag === null) {
            $cache->set($key, $cur_requests, $wait_reset_seconds);
            $cache->set($key . 'flag', 1, $wait_reset_seconds);
        }

        $this->cur_requests = $cur_requests;
        if ($cur_requests < $max_requests) {   // 允许访问
            $cache->inc($key);
            return true;
        }

        $this->wait_seconds = $wait_reset_seconds;
        return false;
    }
}