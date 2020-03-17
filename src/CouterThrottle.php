<?php

declare (strict_types = 1);

namespace think\middleware;

use Closure;
use think\Http;
use think\Request;
use think\Response;

/**
 * 访问频率限制，采用计数器固定窗口算法
 * Class CouterThrottle
 */
class CouterThrottle extends BaseThrottle
{
    protected $wait_seconds = 0;    // 下次合法请求还有多少秒
    protected $now = 0;             // 当前时间戳
    protected $num_requests = 0;    // 规定时间内允许的最大请求次数
    protected $expire = 0;          // 规定时间
    protected $remaining = 0;       // 规定时间内还能请求的次数

    /**
     * 请求是否允许
     * @param $request
     * @return bool
     */
    protected function allowRequest($request)
    {
        $key = $this->getCacheKey($request);
        if (null === $key) {
            return true;
        }
        list($num_requests, $duration) = $this->parseRate($this->config['visit_rate']);
        $now = time();
        $key .= floor($now / $duration);

        $cur_requests = $this->cache->get($key, null);

        if ($cur_requests === null) {
            $cur_requests = 1;
            $this->cache->set($key, $cur_requests, $duration);
        }

        if ($cur_requests <= $num_requests) {   // 允许访问
            $this->cache->inc($key);
            $this->now = $now;
            $this->num_requests = $num_requests;
            $this->expire = $duration;
            $this->remaining = $num_requests - $cur_requests;
            return true;
        }

        $this->wait_seconds = $duration - $now % $duration;
        return false;
    }

    /**
     * 处理限制访问
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        $allow = $this->allowRequest($request);
        if (!$allow) {
            throw $this->buildLimitException($this->wait_seconds);
        }

        $response = $next($request);
        if (200 == $response->getCode()) {
            // 将速率限制 headers 添加到响应中
            $response->header([
                'X-Rate-Limit-Limit' => $this->num_requests,
                'X-Rate-Limit-Remaining' => $this->remaining < 0 ? 0 : $this->remaining,
                'X-Rate-Limit-Reset' => $this->now + $this->expire,
            ]);
        }
        return $response;
    }
}