<?php

declare (strict_types = 1);

namespace think\middleware;

use Closure;
use think\Cache;
use think\Config;
use think\Request;
use think\Response;

/**
 * 访问频率限制
 * Class Throttle
 * @package think\middleware
 */
class Throttle
{
    /**
     * 缓存对象
     * @var Cache
     */
    protected $cache;

    /**
     * 配置参数
     * @var array
     */
    protected $config = [
        // 节流规则 true为自动规则
        'key'    => true,
        // 节流频率 null 表示不限制 eg: 10/m  20/h  300/d
        'visit_rate' => null,
        // 访问受限时返回的http状态码
        'visit_fail_code' => 403,
        // 访问受限时访问的文本信息
        'visit_fail_text' => '访问频率受到限制，请稍等__WAIT__秒再试',

    ];

    protected $wait_seconds = 0;

    protected $duration = [
        's' => 1,
        'm' => 60,
        'h' => 3600,
        'd' => 86400,
    ];

    public function __construct(Cache $cache, Config $config)
    {
        $this->cache  = $cache;
        $this->config = array_merge($this->config, $config->get('throttle', []));
    }

    /**
     * 生成缓存的 key
     * @param Request $request
     * @return null|string
     */
    protected function getCacheKey($request)
    {
        $key = $this->config['key'];
        if ($key instanceof \Closure) {
            $key = call_user_func($key, $this, $request);
        }

        if (null === $key || false === $key || null === $this->config['visit_rate']) {
            // 关闭当前限制
            return;
        }

        if (true === $key) {
            $key = $request->ip();
        } elseif (false !== strpos($key, '__IP__')) {
            $key = str_replace('__IP__', $request->ip(), $key);
        }

        return md5($key);
    }

    /**
     * 解析频率配置项
     * @param $rate
     * @return array
     */
    protected function parseRate($rate)
    {
        list($num, $period) = explode("/", $rate);
        $num_requests = intval($num);
        $duration = $this->duration[$period];
        return [$num_requests, $duration];
    }

    /**
     * 计算距离下次合法请求还有多少秒
     * @param $history
     * @param $now
     * @param $num_requests
     * @param $duration
     * @return void
     */
    protected function wait($history, $now, $num_requests, $duration)
    {
        $wait_seconds = $history ? $duration - ($now - $history[0]) : $duration;
        if ($wait_seconds <= 0) {
            $wait_seconds = 0;
        }
        $this->wait_seconds = $wait_seconds;
    }

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
        $history = $this->cache->get($key, []);
        $now = time();

        // 移除过期的请求的记录
        $history = array_values(array_filter($history, function ($val) use ($now, $duration) {
            return $val >= $now - $duration;
        }));

        if (count($history) < $num_requests) {
            // 允许访问
            $history[] = $now;
            $this->cache->set($key, $history, $duration);
            return true;
        }

        $this->wait($history, $now, $num_requests, $duration);
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
            // 访问受限
            $code = $this->config['visit_fail_code'];
            $content = str_replace('__WAIT__', $this->wait_seconds, $this->config['visit_fail_text']);
            return Response::create($content)->code($code);
        }
        $response = $next($request);
        return $response;
    }

    public function setRate($rate)
    {
        $this->config['visit_rate'] = $rate;
    }
}
