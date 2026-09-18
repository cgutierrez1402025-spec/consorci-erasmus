<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("applications", function (Blueprint $table) {
            $table->id();
            $table->foreignId("mobility_call_id")->constrained("mobility_calls")->cascadeOnDelete();
            $table->foreignId("educational_center_id")->constrained("educational_centers")->cascadeOnDelete();
            $table->string("first_name");
            $table->string("last_name");
            $table->string("id_document_type", 20)->default("DNI");
            $table->string("id_document_number", 30);
            $table->string("email");
            $table->string("phone", 30);
            $table->date("birth_date")->nullable();
            $table->string("vocational_program");
            $table->string("level", 30)->default("grado_medio");
            $table->string("language_certificate_level", 10)->default("B1");
            $table->decimal("academic_score", 5, 2)->default(0);
            $table->decimal("language_score", 5, 2)->default(0);
            $table->decimal("faculty_report_score", 5, 2)->default(0);
            $table->decimal("interview_score", 5, 2)->default(0);
            $table->decimal("inclusion_factor_score", 5, 2)->default(0);
            $table->decimal("total_score", 6, 2)->default(0);
            $table->string("status", 30)->default("submitted"); // submitted, in_review, scored, admitted, reserve, rejected, withdrawn
            $table->string("cv_path")->nullable();
            $table->text("motivation_letter")->nullable();
            $table->text("notes")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("applications");
    }
};
