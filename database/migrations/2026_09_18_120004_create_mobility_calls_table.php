<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("mobility_calls", function (Blueprint $table) {
            $table->id();
            $table->foreignId("erasmus_project_id")->constrained("erasmus_projects")->cascadeOnDelete();
            $table->string("title");
            $table->string("mobility_type", 50); // vet_student_short, vet_student_long, vet_basic, higher_vet, staff_training
            $table->string("academic_year", 20);
            $table->date("application_start_date");
            $table->date("application_end_date");
            $table->date("provisional_list_date")->nullable();
            $table->date("final_list_date")->nullable();
            $table->integer("total_vacancies")->default(10);
            $table->string("status", 30)->default("open"); // draft, open, evaluating, resolved, closed
            $table->text("requirements")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("mobility_calls");
    }
};
