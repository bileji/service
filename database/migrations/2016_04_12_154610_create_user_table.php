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
            $table->increments('id');

            $table->string('username', 15)->nullable()->comment('用户名');

            $table->integer('cellphone')->nullable()->unsigned()->comment('手机号');

            $table->string('email', 25)->nullable()->comment('邮箱');

            $table->string('password', 32)->default('')->comment('密码');

            $table->string('salt', 8)->default('')->comment('盐值');

            $table->tinyInteger('sex')->default(0)->comment('性别: 0 未设置 1 男 2 女');

            $table->string('avatar', 100)->default('')->comment('头像');

            $table->string('sign_up_ip', 15)->default('')->comment('注册时IP');

            $table->tinyInteger('sign_up_platform')->unsigned()->default(0)->comment('注册平台: 0 unknown 1 web 2 ios 3 android');

            $table->timestamp('created_at')->default('00-00-00 00:00:00')->comment('创建时间');

            $table->timestamp('updated_at')->default('00-00-00 00:00:00')->comment('更新时间');

            $table->timestamp('deleted_at')->default('00-00-00 00:00:00')->comment('删除时间');

            # 建索引
            $table->unique('email');

            $table->unique('username');

            $table->unique('cellphone');
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
