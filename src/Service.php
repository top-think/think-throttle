<?php

namespace think\app;

use think\Service as BaseService;
use think\middleware\Throttle;

class Service extends BaseService
{
    public function register()
    {
        $this->app->middleware->add(Throttle::class);
    }
}
