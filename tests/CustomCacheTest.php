<?php
/**
 * 自定义 cache 类
 */
namespace tests;

use Psr\SimpleCache\CacheInterface;
use think\middleware\Throttle;

class CustomCache implements CacheInterface {
    protected $data = [];

    public function get($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    public function set($key, $value, $ttl = null)
    {
        $this->data[$key] = $value;
    }

    public function delete($key) {}
    public function clear() {}
    public function getMultiple($keys, $default = null) {}
    public function setMultiple($values, $ttl = null) {}
    public function deleteMultiple($keys) {}
    public function has($key) {}
}

class DummyCache implements CacheInterface {
    public function get($key, $default = null)
    {
        return $default;
    }

    public function set($key, $value, $ttl = null) {}
    public function delete($key) {}
    public function clear() {}
    public function getMultiple($keys, $default = null) {}
    public function setMultiple($values, $ttl = null) {}
    public function deleteMultiple($keys) {}
    public function has($key) {}
}


class CustomCacheTest extends BaseTest {

    function visit(int $count): int
    {
        $allowCount = 0;
        for ($i = 0; $i < $count; $i++) {
            $request = new \think\Request();
            $request->setMethod('GET');
            $request->setUrl('/');

            $response = $this->app->http->run($request);
            if ($response->getCode() == 200) {
                $allowCount++;
            }
        }
        return $allowCount;
    }

    function test_custom_cache()
    {
        $cache = new CustomCache();
        $config = $this->get_default_throttle_config();
        $config['visit_rate'] = '10/m';
        $config['key'] = function(Throttle $throttle, \think\Request $request) use ($cache) {
            $throttle->setCache($cache);
            return true;
        };

        $this->set_throttle_config($config);

        $this->assertEquals(10, $this->visit(200));
    }

    function test_dummy_cache()
    {
        $cache = new DummyCache();
        $config = $this->get_default_throttle_config();
        $config['visit_rate'] = '10/m';
        $config['key'] = function(Throttle $throttle, \think\Request $request) use ($cache) {
            $throttle->setCache($cache);
            return true;
        };

        $this->set_throttle_config($config);
        $this->assertEquals(200, $this->visit(200));
    }


}
