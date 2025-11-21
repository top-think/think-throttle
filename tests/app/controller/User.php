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
        return '每个用户60秒最多100次搜索';
    }

    #[RateLimit(rate: "1/m", key: RateLimit::AUTO, message: '每人每分钟只能发1次邮件')]
    public function sendMail(): string
    {
        return '邮件发送成功';
    }

    #[RateLimit(rate: "1/d", key: RateLimit::IP, message: '每个用户每天只能领取一次优惠券')]
    #[RateLimit(rate: '100/d', key: 'coupon', message: '今天的优惠券已经发完，请明天再来')]
    public function coupon(): string
    {
        return '优惠券发送成功';
    }

    #[RateLimit(rate: "1/d", key: RateLimit::SESSION, message: '每个用户每天只能领取一次优惠券')]
    #[RateLimit(rate: '100/d', key: 'coupon', message: '今天的优惠券已经发完，请明天再来')]
    public function coupon2(): string
    {
        return '优惠券发送成功';
    }

    #[RateLimit(rate: "5/d", key: [User::class, 'getMobile'], message: '每个手机号一天最多5条短信')]
    public function sendSms2(): string
    {
        // 当key为变量时，可以使用[类, 静态方法]的方式获取key，例如[UserController::class, 'getMobile']会调用UserController的getMobile()方法的返回值为key
        return '短信发送成功';
    }

    /**
     * 自定义key，获取手机号，必须是静态方法
     * @return string
     */
    public static function getMobile(): string
    {
        return request()->get('mobile');
    }

}