<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyAddsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_adds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('enterprise_id');
            $table->json('ye_copy')->nullable()->comment('企业营业执照复印件');
            $table->json('sd_report')->nullable()->comment('2019年企业所得税年报');
            $table->json('ns_prove')->nullable()->comment('2019年全年纳税证明');
            $table->json('zzs_prove')->nullable()->comment('增值税完税证明');
            $table->json('cp_card')->nullable()->comment('企业公章收据');
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
        Schema::dropIfExists('company_adds');
    }
}
