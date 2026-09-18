<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['applications', 'mobilities'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->string('participant_role', 30)->default('student');
                $table->boolean('is_minor')->default(false);
                $table->string('training_branch', 50)->nullable();
                $table->string('iban', 34)->nullable();
                $table->string('bank_account_holder')->nullable();
                $table->text('fewer_opportunities_reason')->nullable();
            });
        }
        Schema::create('mobility_bonds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mobility_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('concept');
            $table->string('destination_iban', 34);
            $table->string('status', 20)->default('pending');
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observations')->nullable();
            $table->timestamps();
        });
        Schema::create('disciplinary_incidents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('severity', 15);
            $table->text('description');
            $table->date('occurred_at');
            $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('sanction')->nullable();
            $table->string('status', 20)->default('open');
            $table->boolean('excludes_from_resolution')->default(false);
            $table->timestamps();
        });
        Schema::create('tutoring_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mobility_id')->constrained()->cascadeOnDelete();
            $table->string('tutor_scope', 20);
            $table->string('mode', 20);
            $table->dateTime('held_at');
            $table->text('content');
            $table->foreignId('tutor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mobility_withdrawals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mobility_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('occurred_at');
            $table->boolean('after_expenses')->default(false);
            $table->boolean('force_majeure')->default(false);
            $table->decimal('reimbursable_expenses', 10, 2)->default(0);
            $table->decimal('funds_to_return', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('domain_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mobility_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->date('scheduled_for');
            $table->text('message');
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->unique(['mobility_id', 'type', 'scheduled_for']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_notifications');
        Schema::dropIfExists('mobility_withdrawals');
        Schema::dropIfExists('tutoring_sessions');
        Schema::dropIfExists('disciplinary_incidents');
        Schema::dropIfExists('mobility_bonds');
        foreach (['applications', 'mobilities'] as $tableName) {
            Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn(['participant_role', 'is_minor', 'training_branch', 'iban', 'bank_account_holder', 'fewer_opportunities_reason']));
        }
    }
};
