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
use App\Enums\Version;
use App\Http\Responses\Response;
use App\Http\Responses\Status;
use App\Models\Redis\TokenRedis;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use JsonRPC\Server as RpcService;

class Helper
{
    const PARAM_STRICT = 100;

    const TOKEN_PAYLOAD = 200;

    const PLATFORM_ABNORMAL = 300;

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
                if (is_integer($key)) {
                    throw new \Exception(json_encode([]), static::PARAM_STRICT);
                }
                // 尝试解析$token
                if ($key === 'token' && is_string($value) && $value == base64_encode(base64_decode($value))) {
                    $tokenInfo = static::tokenDecrypt($value);
                    if (!empty($tokenInfo['user_id']) && !empty($tokenInfo['token_name']) && !empty($tokenInfo['platform'] && !empty($tokenInfo['token_version']))) {
                        if (!empty($request['params']['platform']) && $tokenInfo['platform'] != $request['params']['platform']) {
                            throw new \Exception(json_encode([]), static::PLATFORM_ABNORMAL);
                        }
                        unset($request['params']['platform']);
                        $tokenRedis = new TokenRedis();
                        $payload = $tokenRedis->getTokenPayload($tokenInfo['user_id'], $tokenInfo['token_name'], $tokenInfo['platform']);
                        if (!empty($payload)) {
                            $request['params'][$key] = $payload;
                            $tokenRedis->extendTokenLife($payload);
                        } else {
                            $otherPayload = $tokenRedis->getLastTokenPayload($tokenInfo['user_id'], $tokenInfo['platform']);
                            throw new \Exception(json_encode($otherPayload), static::TOKEN_PAYLOAD);
                        }
                    }
                } else if ($key === 'token' && is_string($value) && $value != base64_encode(base64_decode($value))) {
                    throw new \Exception(json_encode([]), static::TOKEN_PAYLOAD);
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
            if ($e->getCode() == static::PARAM_STRICT) {
                $response = Response::out(Status::RPC_PARAM_STRICT);
            } else if ($e->getCode() == static::PLATFORM_ABNORMAL) {
                $response = Response::out(Status::TOKEN_ABNORMAL);
            } else {
                if (!empty($payload)) {
                    $response = Response::out(Status::TOKE_SIGN_IN_OTHER_DEVICE);
                } else {
                    $response = Response::out(Status::TOKEN_OUT_OF_TIME);
                }
            }
            return $rpcService->getResponse(['result' => $response], json_decode($rpcRequest, true));
        }
        try {
            $rpcService->attach($service);
            $response = $rpcService->execute();
            Log::info($response);
            return $response;
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
     * @param string $tokenVersion 认证版本
     * @return mixed
     */
    public static function tokenEncrypt($tokenName, $userId, $platform = Platform::WEB, $tokenVersion = Version::TOKEN_VERSION)
    {
        return Crypt::encrypt(implode('|', [$userId, $tokenName, $platform, $tokenVersion]));
    }

    /**
     * token 解密
     * @param string $token token
     * @return array
     */
    public static function tokenDecrypt($token)
    {
        list($userId, $tokenName, $platform, $tokenVersion) = explode('|', Crypt::decrypt($token));
        return ['user_id' => $userId, 'token_name' => $tokenName, 'platform' => $platform, 'token_version' => $tokenVersion];
    }
}