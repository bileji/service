<?php
require_once "./vendor/autoload.php";

use JsonRPC\Client;
$s = microtime(true);

$client = new Client('http://192.168.99.100/rpc/v1.0/reception/user');

$result = $client->execute('signIn', ['shu_c', 123456]);

$e = microtime(true);

var_dump($result);

echo $e-$s;
