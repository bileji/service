<?php
/**
 * this source file is Area.php
 *
 * author: shuc <shuc324@gmail.com>
 * time:   2016-04-17 01-26
 */
namespace App\Models\Mysql;

class Area extends Base
{
    public $table = 'area';

    public $guarded = ['id'];

    // 输出格式
    protected $casts = [
        'id'        => 'int',
        'name'      => 'string',
        'pinyin'    => 'string',
        'parent_id' => 'int',
        'level'     => 'int',
    ];
}