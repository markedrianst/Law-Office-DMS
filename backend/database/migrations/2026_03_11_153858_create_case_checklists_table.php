<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('document_type_id')->nullable()->constrained('documents')->nullOnDelete();

            $table->enum('status', ['todo', 'in-progress', 'done'])->default('todo');
            $table->date('due_date')->nullable();
            // Assignment
            $table->foreignId('assigned_clerk_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assigned_to')->nullable();
            
            $table->text('notes')->nullable();
            $table->boolean('is_out')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['case_id', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_checklists');
    }
};