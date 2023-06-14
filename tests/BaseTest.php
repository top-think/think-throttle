<?php
namespace tests;
use PHPUnit\Framework\TestCase;
use think\App;

abstract class BaseTest extends TestCase {
    static $ROOT_PATH = __DIR__ . "/../vendor/topthink/think";
    static $RUNTIME_PATH = __DIR__ . "/../runtime/";
    static $GLOBAL_MIDDLEWARE_PATH = __DIR__ . "/config/global-middleware.php";
    static $NEED_LOAD_GLOBAL_MIDDLEWARE = true;
    protected $app;
    function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->app = new App(static::$ROOT_PATH);
        $this->app->setRuntimePath(static::$RUNTIME_PATH);
        if (static::$NEED_LOAD_GLOBAL_MIDDLEWARE) {
            $this->load_middleware(static::$GLOBAL_MIDDLEWARE_PATH);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // 每次测试完毕都需要清理 runtime cache 目录，避免影响其他单元测试
        $cache_dir = $this->app->getRuntimePath() . "cache";
        $dirs = glob($cache_dir . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $files = glob($dir . '/*.php');
            foreach ($files as $file) {
                unlink($file);
            }
        }
        // 删除 cache 下的空目录
        foreach ($dirs as $dir) {
            rmdir($dir);
        }
    }

    /**
     * 获取默认的 throttle 基础配置信息
     * @return array
     */
    function get_default_throttle_config(): array {
        static $config = [];    // 默认配置从文件中读取，可以设置为静态变量
        if (!$config) {
            $config = include dirname(__DIR__) . "/src/config.php";
        }
        return $config;
    }

    /**
     * 加载中间件配置文件
     * @param string $file 文件的路径 eg: $this->app->getBasePath() . 'middleware.php'
     * @param string $type 类型：global 全局；route 路由；controller 控制器
     */
    function load_middleware(string $file, string $type = 'global') {
        $this->app->middleware->import(include $file, $type);
    }

    /**
     * 加载 throttle 配置文件
     * @param array $config
     */
    function set_throttle_config(array $config) {
        $this->app->config->set($config, 'throttle');
    }

}
