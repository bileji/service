<?php
/**
 * this source file is Status.php
 *
 * author: shuc <shuc324@gmail.com>
 * time:   2016-04-11 15-50
 */
namespace App\Http\Responses;

class Status
{
    const FAILED = -1000;
    const SUCCESS = 0;
    const PARAM_ERROR = 1000;
    const BUSINESS_ERROR = 2000;

    const USER_NOT_EXIST = 100000;
    const USER_PASSWORD_ERROR = 100010;

    public static $errorMessage = [
        # 系统相关
        self::FAILED              => '系统错误',
        self::SUCCESS             => '执行成功',
        self::PARAM_ERROR         => '参数错误',
        self::BUSINESS_ERROR      => '业务错误',

        # 用户相关
        self::USER_NOT_EXIST      => '用户不存在',
        self::USER_PASSWORD_ERROR => '登录密码错误',
    ];
}