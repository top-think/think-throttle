<?php
declare(strict_types=1);

namespace think\middleware\throttle;

class RateParser
{
    public static array $duration = [
        's' => 1,
        'm' => 60,
        'h' => 3600,
        'd' => 86400,
    ];

    /**
     * 解析频率配置项
     * @param string $rate 频率字符串，如 '10/m', '20/h', '300/d', '100/60'
     * @return array{0: int, 1: int} [最大请求数, 时间窗口秒数]
     * @throws \InvalidArgumentException
     */
    public static function parse(string $rate): array
    {
        $parts = explode("/", $rate);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new \InvalidArgumentException("Invalid rate format: '{$rate}', expected format like '10/m', '20/h', '300/d'");
        }
        [$num, $period] = $parts;
        $max_requests = (int)$num;
        $duration = static::$duration[$period] ?? (int)$period;
        return [$max_requests, $duration];
    }
}
