<?php
/**
 * this source file is UserTest.php
 *
 * author: shuc <shuc324@gmail.com>
 * time:   2016-04-17 22-55
 */
use App\Enums\Platform;
use App\Http\Responses\Status;
use Illuminate\Support\Str;

class UserTest extends BootCase
{
    public function testSignUp()
    {
        $userInfo = $this->initUserInfo();
        $response = $this->rpc('rpc/v1.0/reception/user', ['signUp', ['username' => $userInfo['username'], 'password' => $userInfo['password'], 'extension' => ['sex' => 1, 'sign_up_ip' => '192.168.22.14', 'sign_up_platform' => Platform::WEB]]]);

        $response = json_decode($response, true);
        $this->assertEquals($response['code'], Status::SUCCESS);
    }

    public function testSignIn()
    {
        $response = $this->rpc('rpc/v1.0/reception/user', ['signIn', ['username' => $this->userInfo['username'], 'password' => $this->userInfo['password'], 'platform' => Platform::WEB]]);

        $response = json_decode($response, true);
        $this->assertEquals($response['code'], Status::SUCCESS);
    }

    public function testGetUser()
    {
        $response = $this->rpc('rpc/v1.0/reception/user', ['getUser', ['token' => $this->user['data']['token'], 'platform' => Platform::WEB]]);

        $response = json_decode($response, true);
        $this->assertEquals($response['data']['user']['username'], $this->userInfo['username']);
    }

    public function testTokenAbnormal()
    {
        $response = $this->rpc('rpc/v1.0/reception/user', ['getUser', ['token' => $this->user['data']['token'], 'platform' => Platform::APP]]);

        $response = json_decode($response, true);
        $this->assertEquals($response['code'], Status::TOKEN_ABNORMAL);
    }

    public function testSignOut()
    {
        $response = $this->rpc('rpc/v1.0/reception/user', ['signOut', ['token' => $this->user['data']['token'], 'platform' => Platform::WEB]]);

        $response = json_decode($response, true);
        $this->assertEquals($response['code'], Status::SUCCESS);
    }

    public function testPerfectionProfile()
    {
        $profile = ['nickname' => 'only you'];
        $response = $this->rpc('rpc/v1.0/reception/user', ['perfectionProfile', ['token' => $this->user['data']['token'], 'profile' => $profile, 'platform' => Platform::WEB]]);
        $response = json_decode($response, true);
        $this->assertEquals($response['data']['user']['nickname'], $profile['nickname']);
    }
}