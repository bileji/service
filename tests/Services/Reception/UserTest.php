<?php
/**
 * this source file is UserTest.php
 *
 * author: shuc <shuc324@gmail.com>
 * time:   2016-04-17 22-55
 */
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
        $response = $this->rpc('rpc/v1.0/reception/user', ['signUp', ['username' => $userInfo['username'], 'password' => $userInfo['password'], 'extension' => ['sex' => 1, 'sign_up_ip' => '192.168.22.14', 'sign_up_platform' => 1]]]);

        $response = json_decode($response, true);
        $this->assertEquals($response['code'], 0);
    }
}