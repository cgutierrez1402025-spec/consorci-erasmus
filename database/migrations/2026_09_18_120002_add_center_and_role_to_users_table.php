<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->foreignId("educational_center_id")->nullable()->constrained("educational_centers")->nullOnDelete();
            $table->string("role")->default("center_coordinator"); // superadmin, consortium_coordinator, center_coordinator, evaluator
            $table->string("phone")->nullable();
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropForeign(["educational_center_id"]);
            $table->dropColumn(["educational_center_id", "role", "phone"]);
        });
    }
};
