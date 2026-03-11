<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folder_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users');
            
            $table->enum('type', ['IN', 'OUT', 'PENDING'])->default('PENDING');
            $table->string('from_to')->nullable();
            $table->date('date');
            $table->string('purpose')->nullable();
            $table->string('handled_by')->nullable();
            
            // Approval system
            $table->enum('approval_status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['case_id', 'approval_status']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folder_movements');
    }
};