<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_stage_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_stage_id')->nullable()->constrained('case_stages')->nullOnDelete();
            $table->foreignId('to_stage_id')->constrained('case_stages');
            $table->foreignId('changed_by')->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->index(['case_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_stage_histories');
    }
};