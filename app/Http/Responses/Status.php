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

    const TOKEN_OUT_OF_TIME = 200000;
    const TOKE_SIGN_IN_OTHER_DEVICE = 200010;
    const TOKEN_ABNORMAL = 200020;

    const RPC_PARAM_STRICT = 300000;

    const UPLOAD_IMAGE_MODEL_NOT_ALLOW = 400000;

    public static $errorMessage = [
        # 系统相关
        self::FAILED                       => '系统错误',
        self::SUCCESS                      => '执行成功',
        self::PARAM_ERROR                  => '参数错误',
        self::BUSINESS_ERROR               => '业务错误',

        # 用户相关
        self::USER_NOT_EXIST               => '用户不存在',
        self::USER_PASSWORD_ERROR          => '登录密码错误',

        # Token相关
        self::TOKEN_OUT_OF_TIME            => '无效的登录信息',
        self::TOKE_SIGN_IN_OTHER_DEVICE    => '其它设备登录',
        self::TOKEN_ABNORMAL               => '异常的登录信息',

        # Rpc相关
        self::RPC_PARAM_STRICT             => '请使用严格传参模式',

        # 上传图片相关
        self::UPLOAD_IMAGE_MODEL_NOT_ALLOW => '模块不允许上传图片',
    ];
}