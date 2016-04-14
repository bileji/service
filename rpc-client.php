<?php
require_once "./vendor/autoload.php";

use JsonRPC\Client;
use Illuminate\Support\Str;

$s = microtime(true);

$client = new Client('http://192.168.99.100/rpc/v1.0/reception/user');

$username = Str::random(6);
$result1 = $client->execute('signUp', [$username, 123456, ['sex' => 1, 'sign_up_ip' => '192.168.22.14', 'sign_up_platform' => 1]]);
var_dump($result1);
$result2 = $client->execute('signIn', [$username, 123456, 1]);
var_dump($result2);
$arr = json_decode($result2, true);
//$bool = $client->execute('signOut', [$arr['data']['token']]);
$bool = $client->execute('signOut', ['eyJpdiI6ImVuZ2UzVUFcL2xYWXpnSWdcLzQ1TUczdz09IiwidmFsdWUiOiJoN1RZcElVeVBuZjNnd1FKenhvSXpIWU9VVzQzOTBLOStxQkRKQnRoVFdrPSIsIm1hYyI6ImJhMGJlYjU1MjhiMzdjOGRmOTMwNDBlODdhMzZhODJiOGUxOGNiYTRmYjZmOTE1YjA0ZDc5OWI0NDNiMDE2MTcifQ==']);
var_dump($bool);

$e = microtime(true);

echo $e-$s;
