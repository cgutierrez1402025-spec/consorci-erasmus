<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("erasmus_projects", function (Blueprint $table) {
            $table->id();
            $table->string("project_code", 50)->unique();
            $table->string("title");
            $table->string("call_year", 10);
            $table->string("academic_year", 20);
            $table->date("start_date");
            $table->date("end_date");
            $table->decimal("total_grant_awarded", 12, 2)->default(0);
            $table->decimal("individual_support_grant", 12, 2)->default(0);
            $table->decimal("travel_grant", 12, 2)->default(0);
            $table->decimal("organizational_support_grant", 12, 2)->default(0);
            $table->decimal("inclusion_support_grant", 12, 2)->default(0);
            $table->string("status", 30)->default("active");
            $table->text("notes")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("erasmus_projects");
    }
};
