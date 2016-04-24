<?php
/**
 * this source file is QiniuService.php
 *
 * author: shuc <shuc324@gmail.com>
 * time:   2016-04-21 22-28
 */
namespace App\Http\Services\Helper;

use Qiniu\Auth;
use App\Utils\Helper;
use App\Http\Responses\Response;
use App\Http\Responses\Status;

class QiniuService
{
    /**
     * 获取上传图片令牌
     * @param array $token token信息
     * @param $model
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function token(array $token, $model)
    {
        # 允许上传的模块必须要有对应的回调处理方法
        if (!method_exists($this, $model)) {
            return Response::out(Status::UPLOAD_IMAGE_MODEL_NOT_ALLOW);
        }
        $auth = new Auth(config('qiniu.access_key'), config('qiniu.secret_key'));
        $uploadToken = $auth->uploadToken(config('qiniu.bucket'));
        $unique = Helper::unique($model . $token['user_id']);
        return Response::out(Status::SUCCESS, ['model' => $model, 'upload_token' => $uploadToken, 'unique' => $unique]);
    }

    // todo 回调factory
    public function callback()
    {

        // todo call_user_func_array
    }

    // todo 具体的执行方法
    protected function message()
    {

    }
}