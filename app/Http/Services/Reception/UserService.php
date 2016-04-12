<?php
/**
 * this source file is UserService.php
 *
 * author: shuc <shuc324@gmail.com>
 * time:   2016-04-11 14-55
 */
namespace App\Http\Services\Reception;

use App\Enums\Platform;
use App\Enums\UsernameType;
use App\Models\Mysql\User;
use App\Models\Redis\Token;
use App\Utils\Helper;
use App\Http\Responses\Status;
use App\Http\Responses\Response;

class UserService
{
    public function __construct(Token $token)
    {
        $this->token = $token;
    }

    public function signUp()
    {

    }

    /**
     * 用户登录
     * @param $username
     * @param $password
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
            default:
                $user = current(User::select('id', 'password', 'salt')->whereUsername($username)->get()->toArray());
        }

        if (empty($user)) {
            return Response::out(Status::USER_NOT_EXIST);
        }

        if (Helper::encryptPassword($password, $user['salt']) != $user['password']) {
            return Response::out(Status::USER_PASSWORD_ERROR);
        }

        $tokenName = $this->token->saveToken(['user_id' => $user['id']], $platform);

        return Response::out(Status::SUCCESS, ['token_name' => $tokenName]);
    }

    public function signOut($tokenName, $platform = Platform::WEB)
    {

    }
}