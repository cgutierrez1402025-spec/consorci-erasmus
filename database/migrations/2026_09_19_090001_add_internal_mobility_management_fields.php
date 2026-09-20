<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('mobility_calls', function (Blueprint $table): void {
            $table->string('program_type', 20)->default('GM_STEPV')->after('mobility_type');
        });

        Schema::table('mobilities', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('application_id')->constrained()->nullOnDelete();
            $table->string('mobility_kind', 30)->nullable()->after('participant_type');
            $table->date('actual_start_date')->nullable()->after('start_date');
            $table->date('actual_end_date')->nullable()->after('end_date');
            $table->boolean('pre_financing_paid')->default(false)->after('total_grant_amount');
            $table->date('pre_financing_paid_at')->nullable()->after('pre_financing_paid');
            $table->date('grant_agreement_signed_at')->nullable()->after('grant_agreement_signed');
        });

        Schema::table('host_partners', function (Blueprint $table): void {
            $table->string('company_size', 10)->nullable()->after('sector');
            $table->boolean('offers_allowance')->default(false)->after('company_size');
        });

        Schema::create('mobility_document_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('profile', 30);
            $table->string('phase', 10);
            $table->string('document_type', 60);
            $table->string('label');
            $table->boolean('required')->default(true);
            $table->timestamps();
            $table->unique(['profile', 'phase', 'document_type']);
        });

        Schema::create('mobility_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mobility_id')->constrained()->cascadeOnDelete();
            $table->string('phase', 10);
            $table->string('document_type', 60);
            $table->string('label');
            $table->string('original_filename')->nullable();
            $table->string('storage_path')->nullable();
            $table->string('validation_status', 30)->default('pending_delivery');
            $table->text('coordinator_observations')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['mobility_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobility_documents');
        Schema::dropIfExists('mobility_document_templates');

        Schema::table('host_partners', fn (Blueprint $table) => $table->dropColumn(['company_size', 'offers_allowance']));
        Schema::table('mobilities', fn (Blueprint $table) => $table->dropConstrainedForeignId('user_id')->dropColumn(['mobility_kind', 'actual_start_date', 'actual_end_date', 'pre_financing_paid', 'pre_financing_paid_at', 'grant_agreement_signed_at']));
        Schema::table('mobility_calls', fn (Blueprint $table) => $table->dropColumn('program_type'));
        Schema::table('applications', fn (Blueprint $table) => $table->dropConstrainedForeignId('user_id'));
    }
};
