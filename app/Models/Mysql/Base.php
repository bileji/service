<?php
namespace App\Models\Mysql;

use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    protected $insertTime;

    protected $modifyTime;

    public $timestamps = false;

    protected $autoTime = true;

    protected static function boot()
    {
        static::creating(function (Base $model) {
            if ($model->autoTime) {
                $model->insertTime = time();
            }
        });

        static::saving(function (Base $model) {
            if ($model->autoTime) {
                $model->modifyTime = time();
            }
        });
    }
}