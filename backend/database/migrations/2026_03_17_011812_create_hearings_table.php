<?php
// database/migrations/2024_01_01_000001_create_hearings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hearings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Date and Time - only start time, no end time
            $table->date('hearing_date');
            $table->time('start_time')->nullable(); // Optional - maybe all day event
            
            // Location
            $table->string('location')->nullable();
            $table->foreignId('court_id')->nullable()->constrained()->nullOnDelete();
            
            // Type
            $table->enum('type', [
                'hearing', 
                'meeting', 
                'deadline', 
                'task', 
                'personal',
                'other'
            ])->default('hearing');
            
            // Status
            $table->enum('status', [
                'scheduled', 
                'completed', 
                'cancelled', 
                'rescheduled'
            ])->default('scheduled');
            
            // Assignment
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            
            // Reminders
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            
            // Rescheduling
            $table->foreignId('rescheduled_from_id')->nullable()->constrained('hearings')->nullOnDelete();
            $table->text('reschedule_reason')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('hearing_date');
            $table->index('status');
            $table->index('type');
            $table->index(['case_id', 'hearing_date']);
            $table->index(['assigned_to', 'hearing_date']);
            $table->index(['created_by', 'hearing_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hearings');
    }
};