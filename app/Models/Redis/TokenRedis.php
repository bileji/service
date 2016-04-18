<?php
/**
 * this source file is TokenRedis.php
 *
 * author: shuc <shuc324@gmail.com>
 * time:   2016-04-11 17-39
 */
namespace App\Models\Redis;

use App\Enums\Platform;
use App\Utils\Helper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

class TokenRedis
{
    # token名长度
    const TOKEN_NAME_LENGTH = 6;

    # 平台同时登录人数限制
    const APP_ALLOW_SIGN_IN_USER_NUM = 1;
    const WEB_ALLOW_SIGN_IN_USER_NUM = 1;

    # 平台token redis前缀
    const APP_TOKEN_REDIS_PREFIX = 'user:app:';
    const WEB_TOKEN_REDIS_PREFIX = 'user:web:';

    # token生存时间
    const TOKEN_LIVE_TIME = 2592000;

    private function getUserTokenName($userId, $prefix)
    {
        return $prefix . $userId;
    }

    private function getTokenTrueName($userId, $tokenName, $prefix)
    {
        return str_replace('user', $userId, $prefix) . $tokenName;
    }

    /**
     * 存token
     * @param array $payload ['user_id' => 123456] token信息
     * @param string $platform 平台
     * @return bool|string
     */
    public function saveToken(array $payload, $platform = Platform::WEB)
    {
        $tokenName = Str::random(static::TOKEN_NAME_LENGTH);
        $payload['platform'] = $platform;
        $payload['sign_in_time'] = Helper::microTime();

        if ($platform == Platform::WEB) {
            $prefix = static::WEB_TOKEN_REDIS_PREFIX;
            $signInLimit = static::WEB_ALLOW_SIGN_IN_USER_NUM;
        } else {
            $prefix = static::APP_TOKEN_REDIS_PREFIX;
            $signInLimit = static::APP_ALLOW_SIGN_IN_USER_NUM;
        }

        $userTokenName = $this->getUserTokenName($payload['user_id'], $prefix);
        $tokenTrueName = $this->getTokenTrueName($payload['user_id'], $tokenName, $prefix);

        if (Redis::lpush($userTokenName, $tokenTrueName)) {
            if (Redis::hmset($tokenTrueName, $payload) && Redis::expire($tokenTrueName, static::TOKEN_LIVE_TIME)) {
                # 平台同时登录人数限制
                if (Redis::llen($userTokenName) > $signInLimit) {
                    $popTokenName = Redis::rpop($userTokenName);
                    Redis::del($popTokenName);
                }
                return Helper::tokenEncrypt($tokenName, $payload['user_id'], $platform);
            }
        }
        return false;
    }

    /**
     * 移除token
     * @param string $tokenName token名
     * @param int $userId 用户id
     * @param string $platform 平台
     * @return bool
     */
    public function removeToken($tokenName, $userId, $platform = Platform::WEB)
    {
        $prefix = $platform == Platform::WEB ? static::WEB_TOKEN_REDIS_PREFIX : static::APP_TOKEN_REDIS_PREFIX;
        $userTokenName = $this->getUserTokenName($userId, $prefix);
        $tokenTrueName = $this->getTokenTrueName($userId, $tokenName, $prefix);
        return Redis::del($tokenTrueName) ? (boolean)Redis::lrem($userTokenName, 0, $tokenTrueName) : false;
    }

    /**
     * 取得token负载信息
     * @param int $userId 用户id
     * @param string $tokenName token名
     * @param string $platform 平台
     * @return array
     */
    public function getTokenPayload($userId, $tokenName, $platform = Platform::WEB)
    {
        $prefix = $platform == Platform::WEB ? static::WEB_TOKEN_REDIS_PREFIX : static::APP_TOKEN_REDIS_PREFIX;
        $userTokenName = $this->getUserTokenName($userId, $prefix);
        $tokenTrueName = $this->getTokenTrueName($userId, $tokenName, $prefix);
        $userAllTokenName = Redis::lrange($userTokenName, 0, -1);
        $payload = !empty($userAllTokenName) && in_array($tokenTrueName, $userAllTokenName) ? Redis::hgetall($tokenTrueName) : [];
        !empty($payload) && $payload['token_name'] = $tokenName;
        return !empty($payload) ? $payload : [];
    }

    /**
     * 取得最后登录token负载信息
     * @param int $userId
     * @param string $platform
     * @return array
     */
    public function getLastTokenPayload($userId, $platform = Platform::WEB)
    {
        $prefix = $platform == Platform::WEB ? static::WEB_TOKEN_REDIS_PREFIX : static::APP_TOKEN_REDIS_PREFIX;
        $userTokenName = $this->getUserTokenName($userId, $prefix);
        $userAllTokenName = Redis::lrange($userTokenName, 0, -1);
        if (!empty($userAllTokenName) && $userAllTokenName) {
            foreach ($userAllTokenName as $tokenTrueName) {
                if ($payload = Redis::hgetall($tokenTrueName) && !empty($payload)) {
                    $tokenNamePieces = explode(':', $tokenTrueName);
                    $payload['token_name'] = array_pop($tokenNamePieces);
                    return $payload;
                }
            }
        }
        return !empty($payload) ? $payload : [];
    }
}