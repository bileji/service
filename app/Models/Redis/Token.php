<?php
/**
 * this source file is Token.php
 *
 * author: shuc <shuc324@gmail.com>
 * time:   2016-04-11 17-39
 */
namespace App\Models\Redis;

use App\Enums\Platform;
use App\Utils\Helper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

class Token
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

    private function getTokenTrueName($userId, $tokenName, $prefix)
    {
        return str_replace('user', $userId, $prefix) . $tokenName;
    }

    /**
     * 存token
     * @param array $tokenInfo ['user_id' => 123456] token信息
     * @param string $platform 平台
     * @return bool|string
     */
    public function saveToken(array $tokenInfo, $platform = Platform::WEB)
    {
        $tokenName = Str::random(static::TOKEN_NAME_LENGTH);
        $tokenInfo['platform'] = $platform;
        $tokenInfo['sign_in_time'] = Helper::microTime();

        if ($platform == Platform::WEB) {
            $prefix = static::WEB_TOKEN_REDIS_PREFIX;
            $signInLimit = static::WEB_ALLOW_SIGN_IN_USER_NUM;
        } else {
            $prefix = static::APP_TOKEN_REDIS_PREFIX;
            $signInLimit = static::APP_ALLOW_SIGN_IN_USER_NUM;
        }

        $userTokenName = $prefix . $tokenInfo['user_id'];
        $tokenTrueName = $this->getTokenTrueName($tokenInfo['user_id'], $tokenName, $prefix);

        if (Redis::lpush($userTokenName, $tokenTrueName)) {
            if (Redis::hmset($tokenTrueName, $tokenInfo) && Redis::expire($tokenTrueName, static::TOKEN_LIVE_TIME)) {
                # 平台同时登录人数限制
                if (Redis::llen($userTokenName) > $signInLimit) {
                    $popTokenName = Redis::rpop($userTokenName);
                    Redis::del($this->getTokenTrueName($tokenInfo['user_id'], $popTokenName, $prefix));
                }
                return Helper::tokenEncrypt($tokenName, $tokenInfo['user_id'], $platform);
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
        $tokenTrueName = $this->getTokenTrueName($userId, $tokenName, $prefix);
        return Redis::del($tokenTrueName) ? (boolean)Redis::lrem($prefix . $userId, 0, $tokenTrueName) : false;
    }
}