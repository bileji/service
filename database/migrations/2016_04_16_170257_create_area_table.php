<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateAreaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('area', function (Blueprint $table) {

            $table->increments('id')->comment('自增主键');

            $table->string('name', 20)->default('')->comment('地区名');

            $table->string('pinyin', 20)->default('')->comment('地区拼音');

            $table->integer('parent_id')->default(0)->comment('父级id');

            $table->tinyInteger('level')->default(0)->comment('地区级别: 0 国家 1 省 2 市 3 区');

            $table->timestamp('created_at')->default('00-00-00 00:00:00')->comment('创建时间');

            $table->timestamp('updated_at')->default('00-00-00 00:00:00')->comment('更新时间');

            $table->timestamp('deleted_at')->default('00-00-00 00:00:00')->comment('删除时间');

            $table->index(['name', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('area');
    }
}
