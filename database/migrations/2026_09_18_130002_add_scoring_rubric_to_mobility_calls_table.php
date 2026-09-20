<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobility_calls', function (Blueprint $table) {
            $table->json('scoring_rubric')->nullable()->after('requirements');
        });
    }

    public function down(): void
    {
        Schema::table('mobility_calls', fn (Blueprint $table) => $table->dropColumn('scoring_rubric'));
    }
};
