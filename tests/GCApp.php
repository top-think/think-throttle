<?php
/**
 * 默认的 \think\App 的实例初始化后会在一些地方创建对它的引用，
 * 多数是在静态变量里，这就导致它不能被自动垃圾回收。
 * 因此创建 GCApp 作为其子类，添加清理这些引用的处理的方法。
 */

namespace tests;

use think\App;
use think\initializer\BootService;
use think\initializer\Error;
use think\initializer\RegisterService;
use think\Model;
use think\Validate;


class GCError extends Error {
    /**
     * 从 parent::init() 中移除 register_shutdown_function
     * @param App $app
     */
    public function init(App $app)
    {
        $this->app = $app;
        error_reporting(E_ALL);
        set_error_handler([$this, 'appError']);
        set_exception_handler([$this, 'appException']);
        // register_shutdown_function([$this, 'appShutdown']); // 移除
    }
}

class GCValidate extends Validate {
    public static function cleanMaker() { static::$maker = []; }
}

class GCModel extends Model {
    public static function cleanMaker() { static::$maker = []; }
}

/**
 * 可被自动 gc 的，但需要手动调用 refClear 函数
 * Class GCApp
 * @package tests
 */
class GCApp extends App {
    protected $initializers = [     // 覆盖父类
        GCError::class,             // 去掉 register_shutdown_function
        RegisterService::class,     // 原来就有的
        BootService::class,         // 原来就有的
    ];

    /**
     * 添加清理函数
     * @throws \Exception
     */
    public function refClear()
    {
        $this->route->clear(); // 清理路由规则
        // 清理绑定在 App 的实例
        $names = [];
        foreach ($this->getIterator() as $name=>$_v) {
            $names[] = $name;
        }
        foreach ($names as $name) {
            $this->delete($name);
        }
        // 清理异常 handler
        restore_error_handler();
        restore_exception_handler();
        GCValidate::cleanMaker();
        GCModel::cleanMaker();
    }
}