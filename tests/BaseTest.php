<?php
namespace tests;
use PHPUnit\Framework\TestCase;
use think\App;

class BaseTest extends TestCase {
    static $ROOT_PATH = __DIR__ . "/../vendor/topthink/think";
    static $RUNTIME_PATH = __DIR__ . "/../runtime/";
    protected $app;
    function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->app = new App(static::$ROOT_PATH);
        $this->app->setRuntimePath(static::$RUNTIME_PATH);

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

    /**
     * 为避免警告 No tests found in class "tests\BaseTest".
     * 添加一个恒为成功的测试
     */
    function test_always_success() {
        $this->assertTrue(true);
    }

}
