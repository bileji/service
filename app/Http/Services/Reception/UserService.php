<?php
/**
 * this source file is UserService.php
 *
 * author: shuc <shuc324@gmail.com>
 * time:   2016-04-11 14-55
 */
namespace App\Http\Services\Reception;

use App\Enums\Version;
use App\Utils\Helper;
use App\Enums\Platform;
use App\Enums\UsernameType;
use App\Models\Mysql\User;
use App\Http\Responses\Status;
use App\Http\Responses\Response;
use Illuminate\Support\Str;
use App\Models\Redis\TokenRedis;
use App\Models\Redis\UserRedis;

class UserService
{
    const USER_SALT_LENGTH = 6;

    public function __construct(TokenRedis $tokenRedis, UserRedis $userRedis)
    {
        $this->tokenRedis = $tokenRedis;
        $this->userRedis = $userRedis;
    }

    /**
     * 用户注册
     * @param string|int $username 用户名
     * @param string $password 密码
     * @param array $extension 扩展信息[sing_up_platform => 1 ...]
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function signUp($username, $password, $extension = [])
    {
        $userInfo = [];
        switch (Helper::checkUsernameType($username)) {
            case UsernameType::PHONE:
                $userInfo['cellphone'] = $username;
                break;
            case UsernameType::EMAIL:
                $userInfo['email'] = $username;
                break;
            default:
                $userInfo['username'] = $username;
        }
        $userInfo['salt'] = Str::random(static::USER_SALT_LENGTH);
        $userInfo['password'] = Helper::encryptPassword($password, $userInfo['salt']);

        $userInfo = array_intersect_key(array_merge($userInfo, $extension), User::$contrast);

        // 成功新增用户
        if (($user = User::create($userInfo)->toArray()) && !empty($user)) {
            $platform = isset($userInfo['sign_up_platform']) ? $userInfo['sign_up_platform'] : Platform::UNKNOWN;
            $token = $this->tokenRedis->saveToken(['user_id' => $user['id']], $platform);
            return Response::out(Status::SUCCESS, ['token' => $token]);
        }

        return Response::out(Status::FAILED);
    }

    /**
     * 用户登录
     * @param string $username 用户名(name|phone|email)
     * @param string $password 密码
     * @param string $platform
     * @return \App\Http\Responses\Response
     */
    public function signIn($username, $password, $platform = Platform::WEB)
    {
        switch (Helper::checkUsernameType($username)) {
            case UsernameType::PHONE:
                $user = current(User::select('id', 'password', 'salt')->whereCellphone($username)->get()->toArray());
                break;
            case UsernameType::EMAIL:
                $user = current(User::select('id', 'password', 'salt')->whereEmail($username)->get()->toArray());
                break;
            // todo 第三方登录
            default:
                $user = current(User::select('id', 'password', 'salt')->whereUsername($username)->get()->toArray());
        }

        if (empty($user)) {
            return Response::out(Status::USER_NOT_EXIST);
        }

        if (Helper::encryptPassword($password, $user['salt']) != $user['password']) {
            return Response::out(Status::USER_PASSWORD_ERROR);
        }

        $token = $this->tokenRedis->saveToken(['user_id' => $user['id']], $platform);

        return Response::out(Status::SUCCESS, ['token' => $token]);
    }

    /**
     * 用户退出
     * @param array $token token信息
     * @param string $platform 平台
     * @return bool
     */
    public function signOut(array $token, $platform = Platform::WEB)
    {
        if ($token['platform'] != $platform) {
            return Response::out(Status::TOKEN_ABNORMAL);
        }
        $status = $this->tokenRedis->removeToken($token['token_name'], $token['user_id'], $token['platform']);
        return $status ? Response::out(Status::SUCCESS) : Response::out(Status::FAILED);
    }

    /**
     * 取得用户信息
     * @param array $token token信息
     * @param string $platform 平台
     * @return array|bool
     */
    public function getUser(array $token, $platform = Platform::WEB)
    {
        if ($token['platform'] != $platform) {
            return Response::out(Status::TOKEN_ABNORMAL);
        }
        $user = $this->userRedis->getUser($token['user_id'], Version::TOKEN_VERSION);
        return Response::out(Status::SUCCESS, ['user' => $user]);
    }
}