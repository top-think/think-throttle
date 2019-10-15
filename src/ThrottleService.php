<?php

namespace think\middleware;

use think\Service;

class ThrottleService extends Service
{
    public function register()
    {
        $this->app->middleware->add(Throttle::class);
    }
}
