<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_missing_indexes_only.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =============================================
        // CASES - Add missing composite indexes
        // =============================================
        Schema::table('cases', function (Blueprint $table) {
            $table->index(['assigned_lawyer_id', 'case_status', 'created_at']);
            $table->index(['assigned_clerk_id', 'case_status', 'created_at']);
            $table->index(['client_id', 'case_status']);
            $table->index(['current_stage_id', 'case_status']);
            $table->index(['priority', 'case_status']);
            $table->index(['updated_at']);
        });

        // =============================================
        // CASE ACTIVITY LOGS - Add missing indexes
        // =============================================
        Schema::table('case_activity_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });

        // =============================================
        // CHECKLIST MOVEMENTS - Add missing indexes
        // =============================================
        Schema::table('checklist_movements', function (Blueprint $table) {
            $table->index(['approval_status', 'created_at']);
            $table->index(['recorded_by', 'approval_status']);
            $table->index(['checklist_id', 'approval_status']);
        });

        // =============================================
        // FOLDER MOVEMENTS - Add missing indexes
        // =============================================
        Schema::table('folder_movements', function (Blueprint $table) {
            $table->index(['approval_status', 'created_at']);
            $table->index(['recorded_by', 'approval_status']);
        });

        // =============================================
        // DOCUMENTS - Add indexes
        // =============================================
        Schema::table('documents', function (Blueprint $table) {
            $table->index(['category', 'is_active']);
            $table->index(['requires_approval', 'approval_status']);
            $table->index(['approval_status', 'created_at']);
        });

        // =============================================
        // DOCUMENT APPROVALS - Add missing indexes
        // =============================================
        Schema::table('document_approvals', function (Blueprint $table) {
            $table->index(['requested_by', 'status']);
            $table->index(['approved_by', 'approved_at']);
        });

        // =============================================
        // CASE CHECKLISTS - Add missing indexes
        // =============================================
        Schema::table('case_checklists', function (Blueprint $table) {
            $table->index(['assigned_clerk_id', 'status']);
            $table->index(['document_type_id', 'status']);
            $table->index(['completed_at']);
        });

        // =============================================
        // USERS - Add missing indexes
        // =============================================
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role_id', 'status']);
            $table->index(['last_login']);
        });

        // =============================================
        // LOGIN LOGS - Add indexes
        // =============================================
        Schema::table('login_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['ip_address']);
        });
    }

    public function down(): void
    {
        // CASES
        Schema::table('cases', function (Blueprint $table) {
            $table->dropIndex(['assigned_lawyer_id', 'case_status', 'created_at']);
            $table->dropIndex(['assigned_clerk_id', 'case_status', 'created_at']);
            $table->dropIndex(['client_id', 'case_status']);
            $table->dropIndex(['current_stage_id', 'case_status']);
            $table->dropIndex(['priority', 'case_status']);
            $table->dropIndex(['updated_at']);
        });

        // CASE ACTIVITY LOGS
        Schema::table('case_activity_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['action', 'created_at']);
        });

        // CHECKLIST MOVEMENTS
        Schema::table('checklist_movements', function (Blueprint $table) {
            $table->dropIndex(['approval_status', 'created_at']);
            $table->dropIndex(['recorded_by', 'approval_status']);
            $table->dropIndex(['checklist_id', 'approval_status']);
        });

        // FOLDER MOVEMENTS
        Schema::table('folder_movements', function (Blueprint $table) {
            $table->dropIndex(['approval_status', 'created_at']);
            $table->dropIndex(['recorded_by', 'approval_status']);
        });

        // DOCUMENTS
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['category', 'is_active']);
            $table->dropIndex(['requires_approval', 'approval_status']);
            $table->dropIndex(['approval_status', 'created_at']);
        });

        // DOCUMENT APPROVALS
        Schema::table('document_approvals', function (Blueprint $table) {
            $table->dropIndex(['requested_by', 'status']);
            $table->dropIndex(['approved_by', 'approved_at']);
        });

        // CASE CHECKLISTS
        Schema::table('case_checklists', function (Blueprint $table) {
            $table->dropIndex(['assigned_clerk_id', 'status']);
            $table->dropIndex(['document_type_id', 'status']);
            $table->dropIndex(['completed_at']);
        });

        // USERS
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role_id', 'status']);
            $table->dropIndex(['last_login']);
        });

        // LOGIN LOGS
        Schema::table('login_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['ip_address']);
        });
    }
};