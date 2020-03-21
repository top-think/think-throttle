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

        $this->wait_seconds = $wait_reset_seconds % $duration  + 1;
        if ($limit_flag === null) { // 首次访问
            $cur_requests = 1;
            $cache->set($key, $cur_requests, $wait_reset_seconds);
            $cache->set($key . 'flag', 1, $wait_reset_seconds);
            $this->cur_requests = $cur_requests;
            return true;
        }
        $this->cur_requests = $cur_requests;
        if ($cur_requests < $max_requests) {   // 允许访问
            $cache->inc($key);
            return true;
        }

        return false;
    }
}