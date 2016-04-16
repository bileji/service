<?php
/**
 * this source file is User.php
 *
 * author: shuc <shuc324@gmail.com>
 * time:   2016-04-16 21-29
 */
namespace App\Models\Redis;

use App\Enums\Version;
use App\Models\Mysql\Area;
use Illuminate\Support\Facades\Redis;

class User
{
    # redis缓存用户字段
    const CACHE_FIELDS = [
        # 用户id
        'user_id'   => 0,
        # 用户名
        'username'  => '',
        # 昵称
        'nickname'  => '',
        # 邮箱
        'email'     => '',
        # 电话
        'cellphone' => 0,
        # 性别
        'sex'       => 0,
        # 用户来源
        'origin'    => 0,
        # 头像
        'avatar'    => '',
        # 地区
        'area_name' => '',
        # 地区id
        'area_id'   => 0,
    ];

    const CACHE_PREFIX = 'cache:';

    /**
     * 获取用户缓存键名
     * @param int $userId 用户id
     * @return string
     */
    private function getUserCacheKey($userId)
    {
        return static::CACHE_PREFIX . $userId;
    }

    /**
     * 缓存用户信息
     * @param int $userId 用户id
     * @return array|bool
     */
    public function setUser($userId)
    {
        $user = current(User::select('id', 'cellphone', 'username', 'email', 'sex', 'open_type', 'open_id', 'avatar', 'area_id')->whereId($userId)->get()->toArray());

        if (!empty($user)) {
            $user['user_id'] = $user['id'];
            $user['origin'] = $user['open_type'];
            if ($area = current(Area::select('name')->whereId($user['area_id'])->get())->toArray()) {
                $user['area_name'] = $area['name'];
            }
            $userCache = array_intersect_key($user, static::CACHE_FIELDS);
            return Redis::hmset($this->getUserCacheKey($user), $userCache) ? $userCache : false;
        }
        return false;
    }

    /**
     * 取得用户信息
     * @param int $userId 用户id
     * @param string $tokenVersion token版本号
     * @return array|bool
     */
    public function getUser($userId, $tokenVersion)
    {
        $userCache = Redis::hgetall($this->getUserCacheKey($userId));
        if (empty($userCache) || $tokenVersion != Version::TOKEN_VERSION) {
            $userCache = $this->setUser($userId);
            if (!empty($userCache)) {
                $userCache = array_replace(static::CACHE_FIELDS, $userCache);
            }
        }
        return $userCache;
    }
}