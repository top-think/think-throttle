<?php
declare(strict_types=1);
/**
 * 自定义 cache 类
 */
namespace tests;

use Psr\SimpleCache\CacheInterface;
use think\middleware\Throttle;

class CustomCache implements CacheInterface {
    protected $data = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        $this->data[$key] = $value;
        return true;
    }

    public function delete(string $key): bool { return true; }
    public function clear(): bool { return true; }
    public function getMultiple(iterable $keys, mixed $default = null): iterable { return  [];}
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool { return true; }
    public function deleteMultiple(iterable $keys): bool { return true; }
    public function has(string $key): bool { return true; }
}

class DummyCache implements CacheInterface {
    public function get($key, $default = null): mixed
    {
        return $default;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool { return true; }
    public function delete(string $key): bool { return true; }
    public function clear(): bool { return true; }
    public function getMultiple(iterable $keys, mixed $default = null): iterable { return  [];}
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool { return true; }
    public function deleteMultiple(iterable $keys): bool { return true; }
    public function has(string $key): bool { return true; }
}


class CustomCacheTest extends BaseTest {

    function visit(int $count): int
    {
        $allowCount = 0;
        for ($i = 0; $i < $count; $i++) {
            $request = new \think\Request();
            $request->setMethod('GET');
            $request->setUrl('/');

            $response = $this->get_response($request);
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
