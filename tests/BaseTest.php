<?php
namespace tests;

use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase {
    static $ROOT_PATH = __DIR__ . "/../vendor/topthink/think";
    static $RUNTIME_PATH = __DIR__ . "/../runtime/";

    protected $app;
    protected $throttle_config = [];
    protected $middleware_file = __DIR__ . "/config/global-middleware.php";
    protected $middleware_type = 'global';

    /**
     * thinkphp 一般运行在 php-fpm 模式下，每次处理请求都要重新加载配置文件
     * @param \think\Request $request
     * @return \think\Response
     */
    function get_response(\think\Request $request): \think\Response {
        // 创建 \think\App 对象，设置配置
        $app = new GCApp(static::$ROOT_PATH);
        $app->setRuntimePath(static::$RUNTIME_PATH);

        // 加载中间件
        $app->middleware->import(include $this->middleware_file, $this->middleware_type);
        // 设置 throttle 配置
        $app->config->set($this->throttle_config, 'throttle');

        $response =  $app->http->run($request);
        $app->refClear();
        return $response;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // 每次测试完毕都需要清理 runtime cache 目录，避免影响其他单元测试
        $cache_dir = static::$RUNTIME_PATH . "cache";
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
        unset($cache_dir);
        unset($dirs);
        gc_collect_cycles();    // 进行垃圾回收
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
     * 设置中间件配置文件
     * @param string $file 文件的路径 eg: $this->app->getBasePath() . 'middleware.php'
     * @param string $type 类型：global 全局；route 路由；controller 控制器
     */
    function set_middleware(string $file, string $type = 'global') {
        $this->middleware_file = $file;
        $this->middleware_type = $type;
    }

    /**
     * 设置 throttle 配置
     * @param array $config
     */
    function set_throttle_config(array $config) {
        $this->throttle_config = $config;
    }

}
