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
use Illuminate\Support\Facades\Redis;

class Token
{
    # token名长度
    const TOKEN_NAME_LENGTH = 12;

    # 平台同时登录人数限制
    const APP_ALLOW_SIGN_IN_USER_NUM = 1;
    const WEB_ALLOW_SIGN_IN_USER_NUM = 1;

    # 平台token redis前缀
    const APP_TOKEN_REDIS_PREFIX = 'user:app:';
    const WEB_TOKEN_REDIS_PREFIX = 'user:web:';

    # token生存时间
    const TOKEN_LIVE_TIME = 2592000;

    /**
     * 存token
     * @param array $tokenInfo ['userId' => 123456]
     * @param string $platform
     * @return bool|string
     */
    public function saveToken(array $tokenInfo, $platform = Platform::WEB)
    {
        $tokenName = Helper::randString(static::TOKEN_NAME_LENGTH);
        $tokenInfo['platform'] = $platform;
        $tokenInfo['loginTime'] = Helper::microTime();

        if ($platform == Platform::WEB) {
            $prefix = static::APP_TOKEN_REDIS_PREFIX;
            $signInLimit = static::WEB_ALLOW_SIGN_IN_USER_NUM;
        } else {
            $prefix = static::WEB_TOKEN_REDIS_PREFIX;
            $signInLimit = static::APP_ALLOW_SIGN_IN_USER_NUM;
        }

        $userTokenName = $prefix . $tokenInfo['userId'];

        if (Redis::lpush($userTokenName, $prefix . $tokenName)) {
            if (Redis::hmset($prefix . $tokenName, $tokenInfo) && Redis::expire($prefix . $tokenName, static::TOKEN_LIVE_TIME)) {
                # 平台同时登录人数限制
                if (Redis::llen($userTokenName) > $signInLimit) {
                    $popTokenName = Redis::rpop($userTokenName);
                    Redis::del($prefix . $popTokenName);
                }
                return $tokenName;
            }
        }
        return false;
    }
}