<?php
/**
 * this source file is Helper.php
 *
 * author: shuc <shuc324@gmail.com>
 * time:   2016-04-11 14-57
 */
namespace App\Utils;

use App\Enums\UsernameType;
use JsonRPC\Server as RpcService;

class Helper
{
    /**
     * 时间(耗秒级)
     * @return mixed
     */
    public static function microTime()
    {
        return microtime(true) * 10000;
    }

    /**
     * 绑定服务
     * @param $service
     * @return string
     * @throws \Exception
     */
    public static function attachService($service)
    {
        $rpcService = new RpcService();
        $rpcService->attach($service);
        return $rpcService->execute();
    }

    /**
     * 检查用户名类型
     * @param $username
     * @return string name|phone|email 昵称|手机号|邮箱
     */
    public static function checkUsernameType($username)
    {
        $usernameType = UsernameType::NAME;
        if (preg_match('/^1[3|4|5|7|8]\d{9}$/', $username)) {
            $usernameType = UsernameType::PHONE;
        } else if (preg_match('/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/', $username)) {
            $usernameType = UsernameType::EMAIL;
        }
        return $usernameType;
    }

    /**
     * 密码加密
     * @param $password
     * @param $salt
     * @return string
     */
    public static function encryptPassword($password, $salt)
    {
        return md5(md5(trim($password)) . $salt);
    }

    /**
     * 随机字符串
     * @param $length
     * @return string
     */
    public static function randString($length)
    {
        return mb_substr(str_repeat(md5(time()), $length / 32 + 1), 0, $length);
    }

}