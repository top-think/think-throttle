<?php
declare(strict_types=1);

namespace tests;

use think\middleware\throttle\RateParser;

class RateParserTest extends Base
{
    function test_parse_valid_rate()
    {
        $this->assertEquals([10, 60], RateParser::parse('10/m'));
        $this->assertEquals([20, 3600], RateParser::parse('20/h'));
        $this->assertEquals([300, 86400], RateParser::parse('300/d'));
        $this->assertEquals([5, 1], RateParser::parse('5/s'));
        $this->assertEquals([100, 300], RateParser::parse('100/300'));
    }

    function test_parse_invalid_rate_no_slash()
    {
        $this->expectException(\InvalidArgumentException::class);
        RateParser::parse('10m');
    }

    function test_parse_invalid_rate_empty_num()
    {
        $this->expectException(\InvalidArgumentException::class);
        RateParser::parse('/m');
    }

    function test_parse_invalid_rate_empty_period()
    {
        $this->expectException(\InvalidArgumentException::class);
        RateParser::parse('10/');
    }

    function test_parse_invalid_rate_multiple_slashes()
    {
        $this->expectException(\InvalidArgumentException::class);
        RateParser::parse('10/m/h');
    }

    function test_unlimited_rate()
    {
        $config = $this->get_default_throttle_config();
        $config['visit_rate'] = '';
        $this->set_throttle_config($config);
        $allowCount = 0;
        for ($i = 0; $i < 200; $i++) {
            $request = $this->create_request('/');
            if ($this->visit_with_http_code($request)) {
                $allowCount++;
            }
        }
        $this->assertEquals(200, $allowCount);
    }

    function test_custom_seconds_rate()
    {
        $config = $this->get_default_throttle_config();
        $config['visit_rate'] = '5/300';
        $this->set_throttle_config($config);
        $allowCount = 0;
        for ($i = 0; $i < 200; $i++) {
            $request = $this->create_request('/');
            if ($this->visit_with_http_code($request)) {
                $allowCount++;
            }
        }
        $this->assertEquals(5, $allowCount);
    }
}
