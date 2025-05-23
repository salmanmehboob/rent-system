<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('buildings');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('agreement_id')->constrained('agreements');
            $table->integer('year');
            $table->string('month');
            $table->string('rent_amount');
            $table->string('previous_dues');
            $table->string('sub_total');
            $table->string('payable_amount');
            $table->string('current_dues');
            $table->enum('status', ['Paid','Unpaid','Partially Paid']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
