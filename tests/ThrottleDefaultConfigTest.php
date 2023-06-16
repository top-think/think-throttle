<?php


namespace tests;

/**
 * 默认配置的单元测试
 * Class ThrottleDefaultConfig
 * @package tests
 */
class ThrottleDefaultConfigTest extends BaseTest
{
    function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->set_throttle_config($this->get_default_throttle_config());
    }

    function test_visit_rate()
    {
        // 默认的访问频率为 '100/m'
        $allowCount = 0;
        for ($i = 0; $i < 200; $i++) {
            $request = new \think\Request();
            $request->setMethod('GET');
            $request->setUrl('/');

            $response = $this->get_response($request);
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

            $response = $this->get_response($request);
            if ($response->getCode() == 200) {
                $allowCount++;
            }
        }
        $this->assertEquals(200, $allowCount);
    }

}
