<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_no')->unique();
            $table->string('case_code')->unique();
            $table->string('title');
            
            // Foreign keys
            $table->foreignId('category_id')->nullable()->constrained('case_categories')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('assigned_lawyer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_clerk_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('current_stage_id')->nullable()->constrained('case_stages')->nullOnDelete();
            
            // Case details
            $table->string('court_or_office')->nullable();
            $table->string('docket_no')->nullable();
            $table->enum('priority', ['low', 'normal', 'urgent'])->default('normal');
            $table->enum('case_status', ['active', 'closed', 'archived', 'pending', 'rejected'])->default('active');
            $table->text('summary')->nullable();
            
            // Tracking
            $table->boolean('is_out')->default(false);
            $table->foreignId('created_by')->constrained('users');
            
            $table->timestamps();
            
            // Indexes
            $table->index('case_code');
            $table->index('case_status');
            $table->index('priority');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};