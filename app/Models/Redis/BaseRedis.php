<?php
/**
 * this source file is BaseRedis.php
 *
 * author: shuc <shuc324@gmail.com>
 * time:   2016-04-20 16-41
 */
namespace App\Models\Redis;

use Illuminate\Support\Facades\Redis;

class BaseRedis
{
    protected $redis;

    public function __construct($config = 'default')
    {
        $this->redis = Redis::connection($config);
    }
}