<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('title')->unique()->comment('单位名称');
            $table->string('slug')->comment('纳税人识别号');
            $table->string('operator')->nullable()->comment('经办人');
            $table->string('phone')->nullable()->comment('联系电话');
            $table->string('lg_class')->nullable()->comment('大类');
            $table->string('sm_class')->nullable()->comment('小类');
            $table->dateTime('start_at')->nullable()->comment('复工时间');
            $table->integer('users')->default(0)->comment('员工数');

            $table->double('ye_shouru')->nullable()->comment('营业收入');
            $table->double('total_money')->nullable()->comment('资产总额');
            $table->double('zz_shui')->nullable()->comment('增值税');
            $table->double('sd_shui')->nullable()->comment('所得税');
            $table->double('zz_sd_total')->nullable()->comment('增值税和企业所得税总和');
            $table->double('two_zz')->nullable()->comment('开工后次月两个月后增值税');

            $table->string('bank_type')->nullable()->comment('开户银行');
            $table->string('bank_num')->nullable()->comment('开户银行账号');

            $table->decimal('money')->default(0)->comment('补助的金额');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company');
    }
}
