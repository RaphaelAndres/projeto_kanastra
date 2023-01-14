<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charge_notifications', function (Blueprint $table) {
            $table->id();

            $table->timestamp('charged_at')->useCurrent();
            $table->date('boleto_generation_date');
            $table->date('boleto_expiry_date');

            $table->string('boleto_code');
            $table->double('boleto_amount');
            $table->string('boleto_customer_document');
            
            $table->foreignId('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('invoices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('charge_notifications');
    }
};
