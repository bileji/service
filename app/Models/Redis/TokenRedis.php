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

class TokenRedis extends BaseRedis
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

    private function getRedisTokenPrefix($platform = Platform::WEB)
    {
        return $platform == Platform::WEB ? static::WEB_TOKEN_REDIS_PREFIX : static::APP_TOKEN_REDIS_PREFIX;
    }

    private function getSignInLimit($platform = Platform::WEB)
    {
        return $platform == Platform::WEB ? static::WEB_ALLOW_SIGN_IN_USER_NUM : static::APP_ALLOW_SIGN_IN_USER_NUM;
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

        $prefix = $this->getRedisTokenPrefix($platform);
        $signInLimit = $this->getSignInLimit($platform);

        $userTokenName = $this->getUserTokenName($payload['user_id'], $prefix);
        $tokenTrueName = $this->getTokenTrueName($payload['user_id'], $tokenName, $prefix);

        if ($this->redis->lpush($userTokenName, $tokenTrueName)) {
            if ($this->redis->hmset($tokenTrueName, $payload) && $this->redis->expire($tokenTrueName, static::TOKEN_LIVE_TIME)) {
                # 平台同时登录人数限制
                if ($this->redis->llen($userTokenName) > $signInLimit) {
                    $popTokenName = $this->redis->rpop($userTokenName);
                    $this->redis->del($popTokenName);
                }
                return Helper::tokenEncrypt($tokenName, $payload['user_id'], $platform);
            }
        }
        return false;
    }

    /**
     * 延长token寿命
     * @param array $payload
     * @return bool
     */
    public function extendTokenLife(array $payload)
    {
        $prefix = $this->getRedisTokenPrefix($payload['platform']);
        $tokenTrueName = $this->getTokenTrueName($payload['user_id'], $payload['token_name'], $prefix);
        return $this->redis->expire($tokenTrueName, static::TOKEN_LIVE_TIME) ? true : false;
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
        return $this->redis->del($tokenTrueName) ? (boolean)$this->redis->lrem($userTokenName, 0, $tokenTrueName) : false;
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
        $userAllTokenName = $this->redis->lrange($userTokenName, 0, -1);
        $payload = !empty($userAllTokenName) && in_array($tokenTrueName, $userAllTokenName) ? $this->redis->hgetall($tokenTrueName) : [];
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
        $userAllTokenName = $this->redis->lrange($userTokenName, 0, -1);
        if (!empty($userAllTokenName) && $userAllTokenName) {
            foreach ($userAllTokenName as $tokenTrueName) {
                if ($payload = $this->redis->hgetall($tokenTrueName) && !empty($payload)) {
                    $tokenNamePieces = explode(':', $tokenTrueName);
                    $payload['token_name'] = array_pop($tokenNamePieces);
                    return $payload;
                }
            }
        }
        return !empty($payload) ? $payload : [];
    }
}