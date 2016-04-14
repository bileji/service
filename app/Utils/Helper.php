<?php
/**
 * this source file is Helper.php
 *
 * author: shuc <shuc324@gmail.com>
 * time:   2016-04-11 14-57
 */
namespace App\Utils;

use App\Enums\UsernameType;
use App\Http\Responses\Response;
use App\Http\Responses\Status;
use Illuminate\Support\Facades\Log;
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
        $rpcRequest = file_get_contents('php://input');
        $rpcService = new RpcService($rpcRequest);
        try {
            Log::info('rpc request service with: ' . $rpcRequest);
            $rpcService->attach($service);
            return $rpcService->execute();
        } catch (\Exception $e) {
            Log::emerg('service throw exception: ' . $e->getMessage() . ', file: ' . $e->getFile() . ' +' . $e->getLine());
            return $rpcService->getResponse(['result' => Response::out(Status::FAILED)], json_decode($rpcRequest, true));
        }
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
     * @param string|null $password
     * @param string $salt
     * @return string
     */
    public static function encryptPassword($password = null, $salt)
    {
        empty($password) && $password = $salt;
        return md5(md5(trim($password)) . $salt);
    }
}