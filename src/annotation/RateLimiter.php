<?php

namespace think\middleware\annotation;
use think\middleware\throttle\CounterFixed;
use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class RateLimiter
{
    const AUTO = true;
    const IP = '__IP__';
    public function __construct(public string $rate, public mixed $key = RateLimiter::AUTO, public string $driver=CounterFixed::class, public string $message='Too Many Requests')
    {

    }
}