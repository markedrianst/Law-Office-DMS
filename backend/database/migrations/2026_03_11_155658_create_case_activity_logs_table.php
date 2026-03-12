<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_activity_logs', function (Blueprint $table) {
            $table->id();
            
            // CHANGE THIS LINE:
            // From: cascadeOnDelete() - DELETES logs
            // To: nullOnDelete() - KEEPS logs, sets case_id to NULL
            $table->foreignId('case_id')
                    ->nullable()
                  ->constrained()
                  ->nullOnDelete();  
            
            $table->foreignId('user_id')->constrained('users');
            $table->string('action');
            $table->text('details')->nullable();
            $table->timestamps();
            
            $table->index(['case_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_activity_logs');
    }
};