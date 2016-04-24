<?php

use App\Enums\Platform;
use JsonRPC\Client;
use Illuminate\Support\Str;
use Laravel\Lumen\Testing\TestCase;

class BootCase extends TestCase
{
    protected $user = [];

    protected $userInfo = [];

    public function createApplication()
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    public function rpc($route, $payload = [])
    {
        $client = new Client(config('rpc.service.host') . $route);

        return $client->execute($payload[0], $payload[1]);
    }

    protected function initUserInfo()
    {
        return $this->userInfo = ['username' => Str::random(8), 'password' => 123456];
    }

    public function setUp()
    {
        parent::setUp();
        $userInfo = $this->initUserInfo();
        $response = $this->rpc('rpc/v1.0/reception/user', ['signUp', ['username' => $userInfo['username'], 'password' => $userInfo['password'], 'extension' => ['sex' => 1, 'sign_up_ip' => '192.168.22.14', 'sign_up_platform' => Platform::WEB]]]);
        $this->user = json_decode($response, true);
    }
}
