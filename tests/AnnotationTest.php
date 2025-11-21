<?php

namespace tests;

class Request extends \think\Request
{
    // 真实ip可自定义，以便模拟不同地址请求
    public function SetRealIP($ip)
    {
        $this->realIP = $ip;
    }
}

function create_request(string $uri, string $method = 'GET', string $host = '127.0.0.1', array $data = [], array $headers = []): Request
{
    $request = new Request();
    $request->setMethod($method);
    $request->setHost($host);
    $request->setDomain($host);
    $request->setUrl(sprintf('https://%s/%s', $host, $uri));
    $request->withPost($data);
    $request->withHeader($headers);

    // uri 中提取 path info
    $path = strpos($uri, '?') ? strstr($uri, '?', true) : $uri;
    $request->setBaseUrl($path);
    $path_info = empty($path) || '/' == $path ? '' : ltrim($path, '/');
    $request->setPathinfo($path_info);
    return $request;
}

class AnnotationTest extends Base
{
    protected string $middleware_type = 'route';

    function test_annotation_index()
    {
        $this->assertEquals(10, $this->visit_uri_success_count('/user/index', 200));
    }

    function test_annotation_search()
    {
        $this->assertEquals(100, $this->visit_uri_success_count('/user/search', 200));
    }

    function test_annotation_sendmail()
    {
        $this->assertEquals(1, $this->visit_uri_success_count('/user/sendmail', 200));
    }

    function test_annotation_coupon()
    {
        $uri = '/user/coupon';
        $ips = ['127.0.0.1', '127.0.0.2', '127.0.0.3'];
        $totalCount = 0;
        $result = [];
        foreach ($ips as $ip) {
            for ($i = 0; $i < 10; $i++) {
                $request = create_request($uri);
                $request->SetRealIP($ip);
                if ($this->visit_with_http_code($request)) {
                    if (!isset($result[$ip])) {
                        $result[$ip] = 0;
                    }
                    $totalCount++;
                    $result[$ip]++;
                }
            }
        }
        $this->assertEquals(count($ips), $totalCount);
        $this->assertEquals(count($ips), count($result));
        foreach ($result as $count) {
            $this->assertEquals(1, $count);
        }
        // 继续领取直到所有优惠券都领完
        for ($i = 0; $i < 200; $i++) {
            $request = create_request($uri);
            $request->SetRealIP(sprintf('127.0.0.%d', $i));
            if ($this->visit_with_http_code($request)) {
                $totalCount++;
            }
        }
        $this->assertEquals(100, $totalCount);
    }

    // 注解支持使用[类, 静态方法]的方式获取key
    function test_class_method_key() {
        $this->assertEquals(5, $this->visit_uri_success_count('/user/sendSms2?mobile=123', 50));
        $this->assertEquals(5, $this->visit_uri_success_count('/user/sendSms2?mobile=456', 20));
        $this->assertEquals(5, $this->visit_uri_success_count('/user/sendSms2?mobile=789', 20));
    }

}