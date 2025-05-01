<?php

namespace tests\gc;

use think\App;
use think\initializer\Error;

class GCError extends Error
{
    /**
     * 从 parent::init() 中移除 register_shutdown_function
     * @param App $app
     */
    public function init(App $app): void
    {
        $this->app = $app;
        error_reporting(E_ALL);
        set_error_handler([$this, 'appError']);
        set_exception_handler([$this, 'appException']);
        // register_shutdown_function([$this, 'appShutdown']); // 移除
    }
}
