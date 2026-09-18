<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("mobility_payments", function (Blueprint $table) {
            $table->id();
            $table->foreignId("mobility_id")->constrained("mobilities")->cascadeOnDelete();
            $table->string("payment_type", 50)->default("first_payment_80"); // first_payment_80, final_balance_20, travel_reimbursement, other
            $table->decimal("amount", 10, 2);
            $table->date("scheduled_date");
            $table->date("paid_date")->nullable();
            $table->string("status", 30)->default("pending"); // pending, approved, paid, cancelled
            $table->string("reference_number", 100)->nullable();
            $table->text("notes")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("mobility_payments");
    }
};
