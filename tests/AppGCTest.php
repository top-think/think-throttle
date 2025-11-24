<?php
declare(strict_types=1);

namespace tests;

/**
 * APP 对象自动垃圾回收测试
 */
class AppGCTest extends Base {

    public function testAppGC() {
        $count = 5000;
        $total = 0;
        for ($i = 0; $i < $count; $i++) {
            $request = $this->create_request("/hello/name");
            $response = $this->get_response($request);
            if ($response->getCode() == 200) {
                $total += 1;
            }
        }
        $this->assertEquals($count, $total);
    }
}

