<?php
use App\Enums\Platform;
use App\Http\Responses\Status;

/**
 * this source file is QiniuTest.php
 *
 * author: shuc <shuc324@gmail.com>
 * time:   2016-04-24 21-27
 */
class QiniuTest extends BootCase
{
    public function testToken()
    {
        $response = $this->rpc('rpc/v1.0/helper/qiniu', ['token', ['token' => $this->user['data']['token'], 'model' => 'message', 'platform' => Platform::WEB]]);
        $response = json_decode($response, true);
        $this->assertEquals($response['code'], Status::SUCCESS);
    }
}