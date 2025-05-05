<?php
declare(strict_types=1);

namespace think\middleware\annotation;

use Attribute;
use Closure;
use think\middleware\throttle\CounterFixed;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class RateLimit
{
    const AUTO = true;
    const IP = '__IP__';
    const SESSION = '__SESSION__';
    const CONTROLLER = '__CONTROLLER__';
    const ACTION = '__ACTION__';

    public function __construct(public string              $rate,
                                public string|bool|Closure $key = RateLimit::AUTO,
                                public string              $driver = CounterFixed::class,
                                public string              $message = 'Too Many Requests')
    {

    }
}