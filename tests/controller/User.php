<?php

namespace app\controller;

use app\BaseController;
use think\middleware\annotation\RateLimit;

class User extends BaseController
{

    #[RateLimit(rate: "10/m")]
    public function index(): string
    {
        // 默认为IP限流，默认单位时间为1秒
        return '每个ip每秒最多10个请求';
    }

    #[RateLimit(rate: "100/m", key: RateLimit::AUTO)]
    public function search(): string
    {
        // key: RateLimit::UID，以用户ID为维度进行限流，要求session('user.id')不为空
        return '每个用户60秒最多100次搜索';
    }

    #[RateLimit(rate: "1/m", key: RateLimit::AUTO, message: '每人每分钟只能发1次邮件')]
    public function sendMail(): string
    {
        // key: RateLimit::SID，以session_id为维度进行限流
        return '邮件发送成功';
    }

    #[RateLimit(rate: "1/d", key: RateLimit::IP, message: '每个用户每天只能领取一次优惠券')]
    #[RateLimit(rate: '100/d', key: 'coupon', message: '今天的优惠券已经发完，请明天再来')]
    public function coupon(): string
    {
        // key: 'coupon'， 这里coupon为自定义key，也就是全局以coupon为key进行限流，每天最多发100张优惠券
        // 同时以用户ID为维度进行限流，每个用户每天只能领取一次优惠券
        return '优惠券发送成功';
    }

}