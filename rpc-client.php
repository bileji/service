<?php
require_once "./vendor/autoload.php";

use JsonRPC\Client;
use Illuminate\Support\Str;

$s = microtime(true);

$client = new Client('http://192.168.99.100/rpc/v1.0/reception/user');

//$result = $client->execute('signIn', ['shu_c', 123456]);
$result = $client->execute('signUp', [Str::random(6), 123456, ['sex' => 1, 'sign_up_ip' => '192.168.22.14', 'sign_up_platform' => 1]]);

$e = microtime(true);

var_dump($result);

echo $e-$s;
