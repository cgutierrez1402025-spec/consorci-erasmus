<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("educational_centers", function (Blueprint $table) {
            $table->id();
            $table->string("code", 20)->unique();
            $table->string("name");
            $table->string("city");
            $table->string("province")->default("Valencia");
            $table->string("coordinator_name");
            $table->string("coordinator_email");
            $table->string("coordinator_phone")->nullable();
            $table->boolean("is_active")->default(true);
            $table->text("notes")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("educational_centers");
    }
};
