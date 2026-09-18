<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("mobilities", function (Blueprint $table) {
            $table->id();
            $table->foreignId("erasmus_project_id")->constrained("erasmus_projects")->cascadeOnDelete();
            $table->foreignId("mobility_call_id")->constrained("mobility_calls")->cascadeOnDelete();
            $table->foreignId("application_id")->nullable()->constrained("applications")->nullOnDelete();
            $table->foreignId("host_partner_id")->nullable()->constrained("host_partners")->nullOnDelete();
            $table->foreignId("educational_center_id")->constrained("educational_centers")->cascadeOnDelete();
            $table->string("participant_name");
            $table->string("participant_email");
            $table->string("participant_type", 30)->default("student"); // student, staff
            $table->string("destination_country", 5);
            $table->string("destination_city");
            $table->date("start_date");
            $table->date("end_date");
            $table->integer("duration_days");
            $table->string("travel_type", 30)->default("standard"); // standard, green_travel
            $table->boolean("fewer_opportunities")->default(false);
            $table->decimal("daily_grant_rate", 8, 2)->default(0);
            $table->decimal("individual_support_amount", 10, 2)->default(0);
            $table->decimal("travel_amount", 10, 2)->default(0);
            $table->decimal("inclusion_amount", 10, 2)->default(0);
            $table->decimal("total_grant_amount", 10, 2)->default(0);
            $table->string("status", 30)->default("planned"); // planned, contracted, in_progress, completed, cancelled
            $table->boolean("learning_agreement_signed")->default(false);
            $table->boolean("grant_agreement_signed")->default(false);
            $table->boolean("certificate_of_attendance")->default(false);
            $table->boolean("eu_survey_completed")->default(false);
            $table->string("tutor_in_host")->nullable();
            $table->string("tutor_in_origin")->nullable();
            $table->text("notes")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("mobilities");
    }
};
