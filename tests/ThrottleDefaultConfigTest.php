<?php


namespace tests;


/**
 * 默认配置的单元测试
 * Class ThrottleDefaultConfig
 * @package tests
 */
class ThrottleDefaultConfigTest extends BaseTest
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->load_middleware(__DIR__ . "/config/global-middleware.php");
        $default_config = include dirname(__DIR__) . "/src/config.php";
        $this->set_throttle_config($default_config);  // 加载默认配置
    }

    function test_visit_rate()
    {
        // 默认的访问频率为 '100/m'
        $allowCount = 0;
        for ($i = 0; $i < 200; $i++) {
            $request = new \think\Request();
            $request->setMethod('GET');
            $request->setUrl('/');

            $response = $this->app->http->run($request);
            if ($response->getCode() == 200) {
                $allowCount++;
            }
        }
        $this->assertEquals(100, $allowCount);
    }

    function test_unlimited_request_method()
    {
        // 默认只限制了 ['GET', 'HEAD'] ，对 POST 不做限制
        $allowCount = 0;
        for ($i = 0; $i < 200; $i++) {
            $request = new \think\Request();
            $request->setMethod('POST');
            $request->setUrl('/');

            $response = $this->app->http->run($request);
            if ($response->getCode() == 200) {
                $allowCount++;
            }
        }
        $this->assertEquals(200, $allowCount);
    }

}