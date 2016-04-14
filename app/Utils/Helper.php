<?php
/**
 * this source file is Helper.php
 *
 * author: shuc <shuc324@gmail.com>
 * time:   2016-04-11 14-57
 */
namespace App\Utils;

use App\Enums\Platform;
use App\Enums\UsernameType;
use App\Http\Responses\Response;
use App\Http\Responses\Status;
use App\Models\Redis\Token;
use Illuminate\Support\Facades\Crypt;
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
     * 解密token
     * @param array $request
     * @return string
     * @throws \Exception
     */
    private function analysisRequest($request)
    {
        $request = json_decode($request, true);
        if (!empty($request['params'])) {
            foreach ($request['params'] as $key => $value) {
                if (($key == 'token' || $key === 0) && mb_strlen($value, '8bit') >= 216) {
                    // 解析$token或解析第一个参数
                    if ($key === 0 || $key == 'token') {
                        try {
                            $tokenInfo = static::tokenDecrypt($value);
                        } catch (\Exception $e) {

                        }
                        if (!empty($tokenInfo['user_id']) && !empty($tokenInfo['token_name']) && !empty($tokenInfo['platform'])) {
                            $tokenRedis = new Token();
                            $payload = $tokenRedis->getTokenPayload($tokenInfo['user_id'], $tokenInfo['token_name'], $tokenInfo['platform']);
                            if (!empty($payload)) {
                                if ($key === 0) {
                                    $request['params'][$key] = $payload;
                                } else {
                                    $request['params']['payload'] = $payload;
                                    unset($request['params']['token']);
                                }
                            } else {
                                $otherPayload = $tokenRedis->getLastTokenPayload($tokenInfo['user_id'], $tokenInfo['platform']);
                                throw new \Exception(json_encode($otherPayload));
                            }
                        }
                    }
                }
            }
        }
        return json_encode($request);
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
        Log::info('rpc request service with: ' . $rpcRequest);
        try {
            $rpcRequest = (new static)->analysisRequest($rpcRequest);
            $rpcService = new RpcService($rpcRequest);
        } catch (\Exception $e) {
            $rpcService = new RpcService();
            $payload = json_decode($e->getMessage(), true);
            if (!empty($payload)) {
                $response = Response::out(Status::TOKE_SIGN_IN_OTHER_DEVICE);
            } else {
                $response = Response::out(Status::TOKEN_OUT_OF_TIME);
            }
            return $rpcService->getResponse(['result' => $response], json_decode($rpcRequest, true));
        }
        Log::info('rpc request service with: ' . $rpcRequest);
        try {
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

    /**
     * token 加密
     * @param string $tokenName token名
     * @param int $userId 用户id
     * @param string $platform 平台
     * @return mixed
     */
    public static function tokenEncrypt($tokenName, $userId, $platform = Platform::WEB)
    {
        return Crypt::encrypt(implode('|', [$userId, $tokenName, $platform]));
    }

    /**
     * token 解密
     * @param string $token token
     * @return array
     */
    public static function tokenDecrypt($token)
    {
        list($userId, $tokenName, $platform) = explode('|', Crypt::decrypt($token));
        return ['user_id' => $userId, 'token_name' => $tokenName, 'platform' => $platform];
    }
}