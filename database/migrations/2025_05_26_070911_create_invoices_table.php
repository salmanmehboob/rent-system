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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('buildings')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('agreement_id')->constrained('agreements')->onDelete('cascade');
            $table->string('year');
            $table->string('month');
            $table->string('rent_amount');
            $table->string('paid')->nullable();
            $table->string('dues');
            $table->string('remaining');
            $table->string('total');
            $table->enum('status', ['Paid','Unpaid','Partially Paid', 'Dues Adjusted'])->default('Unpaid');
            $table->boolean('is_active')->default(true);
            $table->enum('type', ['Current','Previous'])->default('Current');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
