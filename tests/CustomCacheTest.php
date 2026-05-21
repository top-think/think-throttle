<?php
declare(strict_types=1);
/**
 * 自定义 cache 类
 */

namespace tests;

use DateInterval;
use Psr\SimpleCache\CacheInterface;
use think\middleware\Throttle;
use think\Request;

class CustomCache implements CacheInterface
{
    protected array $data = [];
    protected array $expire = [];

    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($this->expire[$key]) && $this->expire[$key] < time()) {
            unset($this->data[$key], $this->expire[$key]);
            return $default;
        }
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $this->data[$key] = $value;
        if ($ttl !== null) {
            $seconds = $ttl instanceof DateInterval ? (int)$ttl->s : $ttl;
            $this->expire[$key] = time() + $seconds;
        }
        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->data[$key], $this->expire[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->data = [];
        $this->expire = [];
        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]) && (!isset($this->expire[$key]) || $this->expire[$key] >= time());
    }
}

class DummyCache implements CacheInterface
{
    public function get($key, $default = null): mixed
    {
        return $default;
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        return true;
    }

    public function delete(string $key): bool
    {
        return true;
    }

    public function clear(): bool
    {
        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return [];
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        return true;
    }

    public function has(string $key): bool
    {
        return true;
    }
}


class CustomCacheTest extends Base
{

    function test_custom_cache()
    {
        $cache = new CustomCache();
        $config = $this->get_default_throttle_config();
        $config['visit_rate'] = '10/m';
        $config['key'] = function (Throttle $throttle, Request $request) use (&$cache) {
            $throttle->setCache($cache);
            return true;
        };

        $this->set_throttle_config($config);

        $this->assertEquals(10, $this->visit(200));
    }

    function visit(int $count): int
    {
        $allowCount = 0;
        for ($i = 0; $i < $count; $i++) {
            $request = $this->create_request('/');
            if ($this->visit_with_http_code($request)) {
                $allowCount++;
            }
        }
        return $allowCount;
    }

    function test_dummy_cache()
    {
        $cache = new DummyCache();
        $config = $this->get_default_throttle_config();
        $config['visit_rate'] = '10/m';
        $config['key'] = function (Throttle $throttle, Request $request) use (&$cache) {
            $throttle->setCache($cache);
            return true;
        };

        $this->set_throttle_config($config);
        $this->assertEquals(200, $this->visit(200));
    }


}
