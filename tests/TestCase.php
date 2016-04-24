<?php

use JsonRPC\Client;

class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    public function rpc($route, $payload = [])
    {
        $client = new Client(config('rpc.service.host') . $route);
        return $client->execute($payload[0], $payload[1]);
    }
}
