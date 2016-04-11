<?php
namespace App\Models\Mysql;

class User extends Base
{
    public $table = 'user';

    public $guarded = 'id';

    // 输出格式
    public $casts = [
        'id'             => 'int',
        'cellphone'      => 'int',
        'username'       => 'string',
        'password'       => 'string',
        'salt'           => 'string',
        'sex'            => 'int',
        'avatar'         => 'string',
        'email'          => 'string',
        'signUpIp'       => 'string',
        'signUpPlatform' => 'int',
        'insertTime'     => 'int',
        'modifyTime'     => 'int',
    ];
}