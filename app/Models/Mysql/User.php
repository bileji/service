<?php
namespace App\Models\Mysql;

class User extends Base
{
    public $table = 'user';

    public $guarded = ['id'];

    // 对照
    public static $contrast = [
        'cellphone'        => 'int',
        'username'         => 'string',
        'open_id'          => 'string',
        'open_type'        => 'int',
        'password'         => 'string',
        'salt'             => 'string',
        'sex'              => 'int',
        'avatar'           => 'string',
        'email'            => 'string',
        'sign_up_ip'       => 'string',
        'area_id'          => 'int',
        'sign_up_platform' => 'int',
    ];

    // 输出格式
    protected $casts = [
        'id'               => 'int',
        'cellphone'        => 'int',
        'username'         => 'string',
        'open_id'          => 'string',
        'open_type'        => 'int',
        'password'         => 'string',
        'salt'             => 'string',
        'sex'              => 'int',
        'avatar'           => 'string',
        'email'            => 'string',
        'sign_up_ip'       => 'string',
        'area_id'          => 'int',
        'sign_up_platform' => 'int',
        'created_at'       => 'string',
        'updated_at'       => 'string',
        'deleted_at'       => 'string',
    ];
}