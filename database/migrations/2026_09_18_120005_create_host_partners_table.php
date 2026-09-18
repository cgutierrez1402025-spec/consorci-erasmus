<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("host_partners", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("vat_number", 50)->nullable();
            $table->string("country_code", 5);
            $table->tinyInteger("country_group")->default(2); // 1, 2, 3
            $table->string("city");
            $table->string("address")->nullable();
            $table->string("contact_person")->nullable();
            $table->string("contact_email")->nullable();
            $table->string("contact_phone")->nullable();
            $table->string("website")->nullable();
            $table->string("sector")->nullable();
            $table->json("working_languages")->nullable();
            $table->boolean("is_active")->default(true);
            $table->text("notes")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("host_partners");
    }
};
