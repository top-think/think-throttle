<?php

declare (strict_types = 1);

namespace think\middleware;

use think\Cache;
use think\Config;

abstract class BaseThrottle
{
    /**
     * 默认配置参数
     * @var array
     */
    public static $default_config = [
        'prefix' => 'throttle_',                    // 缓存键前缀，防止键与其他应用冲突
        'key'    => true,                           // 节流规则 true为自动规则
        'visit_rate' => null,                       // 节流频率 null 表示不限制 eg: 10/m  20/h  300/d
        'visit_fail_code' => 429,                   // 访问受限时返回的http状态码
        'visit_fail_text' => 'Too Many Requests',   // 访问受限时访问的文本信息
    ];

    public static $duration = [
        's' => 1,
        'm' => 60,
        'h' => 3600,
        'd' => 86400,
    ];

    /**
     * 缓存对象
     * @var Cache
     */
    protected $cache;

    /**
     * 配置参数
     * @var array
     */
    protected $config = [];

    public function __construct(Cache $cache, Config $config)
    {
        $this->cache  = $cache;
        $this->config = array_merge(static::$default_config, $config->get('throttle', []));
    }

    /**
     * 解析频率配置项
     * @param $rate
     * @return array
     */
    protected function parseRate($rate)
    {
        list($num, $period) = explode("/", $rate);
        $num_requests = (int) $num;
        $duration = static::$duration[$period] ?? (int) $period;
        return [$num_requests, $duration];
    }

    /**
     * 设置速率
     * @param $rate string '10/m'  '20/300'
     * @return $this
     */
    public function setRate($rate)
    {
        $this->config['visit_rate'] = $rate;
        return $this;
    }

    /**
     * 设置缓存驱动
     * @param $cache
     * @return $this
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
        return $this;
    }

}