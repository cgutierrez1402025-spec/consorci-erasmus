<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('birth_place')->nullable()->after('birth_date');
            $table->string('nationality', 100)->nullable()->after('birth_place');
            $table->string('address')->nullable()->after('nationality');
            $table->string('locality', 100)->nullable()->after('address');
            $table->string('province', 100)->nullable()->after('locality');
            $table->json('country_preferences')->nullable()->after('province');
            $table->boolean('communications_consent')->default(false)->after('country_preferences');
            $table->unsignedInteger('absence_count')->default(0)->after('inclusion_factor_score');
            $table->decimal('relevant_language_grade', 5, 2)->nullable()->after('absence_count');
            $table->json('score_breakdown')->nullable()->after('total_score');
            $table->string('tie_break_reason')->nullable()->after('score_breakdown');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['birth_place', 'nationality', 'address', 'locality', 'province', 'country_preferences', 'communications_consent', 'absence_count', 'relevant_language_grade', 'score_breakdown', 'tie_break_reason']);
        });
    }
};
