<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {

            $table->increments('id')->comment('自增主键');

            $table->string('username', 15)->nullable()->comment('用户名');

            $table->string('cellphone', 11)->nullable()->comment('手机号');

            $table->string('email', 25)->nullable()->comment('邮箱');

            $table->string('open_id', 32)->nullable()->comment('OpenId');

            $table->tinyInteger('open_type')->default(0)->comment('Open类型: 0 unknown 1 QQ 2 Sina');

            $table->string('nickname', 25)->default('')->comment('昵称');

            $table->string('password', 32)->default('')->comment('密码');

            $table->string('salt', 8)->default('')->comment('盐值');

            $table->tinyInteger('sex')->default(0)->comment('性别: 0 未设置 1 男 2 女');

            $table->string('avatar', 100)->default('')->comment('头像');

            $table->integer('area_id')->default(0)->comment('地区id');

            $table->string('sign_up_ip', 15)->default('0.0.0.0')->comment('注册时IP');

            $table->string('sign_up_platform', 10)->default('unknown')->comment('注册平台: unknown web iphone android');

            $table->timestamp('created_at')->default('00-00-00 00:00:00')->comment('创建时间');

            $table->timestamp('updated_at')->default('00-00-00 00:00:00')->comment('更新时间');

            $table->timestamp('deleted_at')->default('00-00-00 00:00:00')->comment('删除时间');

            # 建索引
            $table->unique('email');

            $table->unique('username');

            $table->unique('cellphone');

            $table->index(['open_id', 'open_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
}
