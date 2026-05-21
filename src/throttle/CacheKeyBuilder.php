<?php
declare(strict_types=1);

namespace think\middleware\throttle;

use Closure;
use think\Container;
use think\middleware\annotation\RateLimit as RateLimitAnnotation;
use think\Request;
use think\Session;

class CacheKeyBuilder
{
    public function __construct(
        protected Session $session,
        protected string  $prefix = 'throttle_',
    ) {
    }

    /**
     * 生成缓存的 key
     * @param Request $request
     * @param string|bool|Closure|array $key
     * @param string $driver
     * @param bool $annotation
     * @return string
     */
    public function build(Request $request, string|bool|array|Closure $key, string $driver, bool $annotation = false): string
    {
        if ($key instanceof Closure) {
            $key = Container::getInstance()->invokeFunction($key, [$this, $request]);
        } elseif (is_array($key)) {
            if (!is_callable($key)) {
                throw new \InvalidArgumentException('The array key must be a callable, e.g. [ClassName::class, "methodName"]');
            }
            $key = call_user_func($key);
        }

        if ($key === false || $key === '') {
            return '';
        }

        if ($key === true) {
            $key = $request->ip();
        } elseif (is_string($key) && str_contains($key, '__')) {
            $key = str_replace(
                [RateLimitAnnotation::CONTROLLER, RateLimitAnnotation::ACTION, RateLimitAnnotation::IP, RateLimitAnnotation::SESSION],
                [$request->controller(), $request->action(), $request->ip(), $this->session->getId()],
                $key
            );
        }

        if ($annotation) {
            $key = $request->controller() . $request->action() . $key;
        }

        return md5($this->prefix . $key . $driver);
    }
}
