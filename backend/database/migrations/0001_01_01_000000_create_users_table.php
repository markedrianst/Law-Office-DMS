<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
  Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // admin, clerk, lawyer
            $table->timestamps();
        });

       
         Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->string('full_name', 120);
            $table->string('email', 120)->unique();
            $table->string('password_hash');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->dateTime('last_login')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->timestamps();
        });

       

        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50);
            $table->text('details')->nullable();
            $table->string('email_attempted', 120);
            $table->string('ip_address', 45);
            $table->string('user_agent', 255);
            $table->enum('status', ['success', 'failed']);
            $table->timestamp('created_at')->useCurrent();
        });

      

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('sessions');
    }
};
