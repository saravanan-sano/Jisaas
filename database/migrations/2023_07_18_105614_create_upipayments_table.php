<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUpiPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upipayments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('customer_vpa');
            $table->decimal('amount', 10, 2);
            $table->string('client_txn_id');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_mobile');
            $table->string('p_info');
            $table->string('upi_txn_id');
            $table->string('status');
            $table->string('remark')->nullable();
            $table->string('plan_id')->nullable();
            $table->string('udf2')->nullable();
            $table->string('udf3')->nullable();
            $table->string('redirect_url');
            $table->timestamp('txnAt');
            $table->timestamps();

            // Define foreign key relationship with companies table
            $table->foreign('company_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upipayments');
    }
}
