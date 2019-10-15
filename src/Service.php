<?php

namespace think\middleware;

use think\Service as BaseService;

class Service extends BaseService
{
    public function register()
    {
        $this->app->middleware->add(Throttle::class);
    }
}
