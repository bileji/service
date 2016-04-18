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

class UserTest extends TestCase
{
    private $userInfo = [];

    private function getUserInfo()
    {
        return $this->userInfo = ['username' => Str::random(8), 'password' => 123456];
    }

    public function testSignUp()
    {
        $userInfo = $this->getUserInfo();
        $response = $this->rpc('rpc/v1.0/reception/user', ['signUp', ['username' => $userInfo['username'], 'password' => $userInfo['password'], 'extension' => ['sex' => 1, 'sign_up_ip' => '192.168.22.14', 'sign_up_platform' => Platform::WEB]]]);

        $response = json_decode($response, true);
        $this->assertEquals($response['code'], Status::SUCCESS);
        return $response;
    }

    public function testSignIn()
    {
        $this->testSignUp();

        $response = $this->rpc('rpc/v1.0/reception/user', ['signIn', ['username' => $this->userInfo['username'], 'password' => $this->userInfo['password'], 'platform' => Platform::WEB]]);

        $response = json_decode($response, true);
        $this->assertEquals($response['code'], Status::SUCCESS);
    }

    public function testGetUser()
    {
        $user = $this->testSignUp();

        $response = $this->rpc('rpc/v1.0/reception/user', ['getUser', ['token' => $user['data']['token'], 'platform' => Platform::WEB]]);

        $response = json_decode($response, true);
        $this->assertEquals($response['data']['user']['username'], $this->userInfo['username']);
    }

    public function testSignOut()
    {
        $user = $this->testSignUp();

        $response = $this->rpc('rpc/v1.0/reception/user', ['signOut', ['token' => $user['data']['token'], 'platform' => Platform::WEB]]);

        $response = json_decode($response, true);
        $this->assertEquals($response['code'], Status::SUCCESS);
    }
}