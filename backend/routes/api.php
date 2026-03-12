<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\MasterData\CaseCategoryController;
use App\Http\Controllers\Admin\MasterData\CourtController;
use App\Http\Controllers\Admin\MasterData\DocumentController;
use App\Http\Controllers\Admin\CaseController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CaseChecklistController;
use App\Http\Controllers\Admin\CaseStageController;
use App\Http\Controllers\Admin\FolderTrackerController;
use App\Http\Controllers\Admin\ChecklistTrackerController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ApprovalsController; 

// ========== PUBLIC ROUTES ==========
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
})->middleware('web');

Route::post('/login', [AuthenticatedSessionController::class, 'login']);
Route::put('/changepassword', [AuthenticatedSessionController::class, 'change']);

// ========== PROTECTED ROUTES ==========
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthenticatedSessionController::class, 'logout']);
    Route::get('/user', [AuthenticatedSessionController::class, 'getUserData']);
    
    // ========== USER MANAGEMENT ==========
    Route::get('/roles', [UserManagementController::class, 'getRoles']);
    Route::get('/users', [UserManagementController::class, 'index']);
    Route::post('/users', [UserManagementController::class, 'store']);
    Route::get('/users/{id}', [UserManagementController::class, 'show']);
    Route::put('/users/{id}', [UserManagementController::class, 'update']);
    Route::delete('/users/{id}', [UserManagementController::class, 'destroy']);
    Route::patch('/users/{id}/toggle-status', [UserManagementController::class, 'toggleStatus']);
    
    // ========== ADMIN ROUTES ==========
    Route::prefix('admin')->group(function () {
        
        // ========== MASTER DATA ==========
        
        // Case Categories
        Route::get('/case-categories', [CaseCategoryController::class, 'index']);
        Route::get('/case-categories/active', [CaseCategoryController::class, 'getActive']);
        Route::get('/case-categories/{id}', [CaseCategoryController::class, 'show']);
        Route::post('/case-categories', [CaseCategoryController::class, 'store']);
        Route::put('/case-categories/{id}', [CaseCategoryController::class, 'update']);
        Route::patch('/case-categories/{id}/toggle', [CaseCategoryController::class, 'toggleActive']);
        Route::delete('/case-categories/{id}', [CaseCategoryController::class, 'destroy']);
        
        // Courts
        Route::get('/courts', [CourtController::class, 'index']);
        Route::get('/courts/active', [CourtController::class, 'getActive']);
        Route::get('/courts/types', [CourtController::class, 'getTypes']);
        Route::get('/courts/{id}', [CourtController::class, 'show']);
        Route::post('/courts', [CourtController::class, 'store']);
        Route::put('/courts/{id}', [CourtController::class, 'update']);
        Route::patch('/courts/{id}/toggle', [CourtController::class, 'toggleActive']);
        Route::delete('/courts/{id}', [CourtController::class, 'destroy']);
        
        // Documents with Approval Workflow
        Route::get('/documents', [DocumentController::class, 'index']);
        Route::get('/documents/active', [DocumentController::class, 'getActive']);
        Route::get('/documents/categories', [DocumentController::class, 'getCategories']);
        Route::get('/documents/pending-approvals', [DocumentController::class, 'getPendingApprovals']);
        Route::get('/documents/{id}', [DocumentController::class, 'show']);
        Route::get('/documents/{id}/approval-history', [DocumentController::class, 'getApprovalHistory']);
        Route::post('/documents', [DocumentController::class, 'store']);
        Route::put('/documents/{id}', [DocumentController::class, 'update']);
        Route::patch('/documents/{id}/approve', [DocumentController::class, 'approve']);
        Route::patch('/documents/{id}/reject', [DocumentController::class, 'reject']);
        Route::post('/documents/bulk-approve', [DocumentController::class, 'bulkApprove']);
        Route::patch('/documents/{id}/toggle', [DocumentController::class, 'toggleActive']);
        Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);
        
        // ========== CLIENTS ==========
        Route::get('/clients', [ClientController::class, 'index']);
        Route::get('/clients/search', [ClientController::class, 'search']);
        Route::get('/clients/{id}', [ClientController::class, 'show']);
        Route::post('/clients', [ClientController::class, 'store']);
        Route::put('/clients/{id}', [ClientController::class, 'update']);
        Route::delete('/clients/{id}', [ClientController::class, 'destroy']);
        Route::get('/clients/{id}/cases', [ClientController::class, 'getCases']);
        
        // ========== CASE MANAGEMENT ==========
        
        // Case Lookups
        Route::get('/case-lookups', [CaseController::class, 'getLookups']);
        
        // Cases CRUD
        Route::get('/cases', [CaseController::class, 'index']);
        Route::post('/cases', [CaseController::class, 'store']);
        Route::get('/cases/{id}', [CaseController::class, 'show']);
        Route::put('/cases/{id}', [CaseController::class, 'update']);
        Route::delete('/cases/{id}', [CaseController::class, 'destroy']);
        Route::patch('/cases/{id}/archive', [CaseController::class, 'archive']);
        Route::get('/cases/{id}/activity-logs', [CaseController::class, 'getActivityLogs']);
        
        // Case Stage
        Route::get('/cases/{caseId}/stages/history', [CaseStageController::class, 'history']);
        Route::put('/cases/{caseId}/stage', [CaseStageController::class, 'updateCaseStage']);
        
        // Case Checklist
        Route::get('/cases/{caseId}/checklist', [CaseChecklistController::class, 'index']);
        Route::post('/cases/{caseId}/checklist', [CaseChecklistController::class, 'store']);
        Route::get('/cases/{caseId}/checklist/{id}', [CaseChecklistController::class, 'show']);
        Route::put('/cases/{caseId}/checklist/{id}', [CaseChecklistController::class, 'update']);
        Route::delete('/cases/{caseId}/checklist/{id}', [CaseChecklistController::class, 'destroy']);
        Route::patch('/cases/{caseId}/checklist/{id}/status', [CaseChecklistController::class, 'updateStatus']);
        
        // Folder Tracker
        Route::get('/cases/{caseId}/folder-tracker', [FolderTrackerController::class, 'index']);
        Route::post('/cases/{caseId}/folder-tracker', [FolderTrackerController::class, 'store']);
        Route::get('/cases/{caseId}/folder-tracker/pending', [FolderTrackerController::class, 'pending']);
        Route::patch('/cases/{caseId}/folder-tracker/{movementId}/approve', [FolderTrackerController::class, 'approve']);
        
        // Checklist Tracker
        Route::get('/cases/{caseId}/checklist-tracker', [ChecklistTrackerController::class, 'index']);
        Route::post('/cases/{caseId}/checklist-tracker', [ChecklistTrackerController::class, 'store']);
        Route::get('/cases/{caseId}/checklist-tracker/pending', [ChecklistTrackerController::class, 'pending']);
        Route::patch('/cases/{caseId}/checklist-tracker/{movementId}/approve', [ChecklistTrackerController::class, 'approve']);
        
        // Audit trail
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::get('/audit-logs/case-activity', [AuditLogController::class, 'caseActivity']);
        Route::get('/audit-logs/combined', [AuditLogController::class, 'combined']);
        Route::get('/audit-logs/actions', [AuditLogController::class, 'getActions']);
        Route::get('/audit-logs/stats', [AuditLogController::class, 'getStats']);
        Route::get('/audit-logs/export', [AuditLogController::class, 'export']);
        Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);
        
            // ========== APPROVALS (Admin & Lawyer only) ==========
        Route::get('/approvals', [ApprovalsController::class, 'index']);
        Route::get('/approvals/pending-count', [ApprovalsController::class, 'pendingCount']);
        Route::patch('/approvals/{type}/{id}/approve', [ApprovalsController::class, 'approve']);
        Route::get('/approvals/case/{caseId}', [ApprovalsController::class, 'caseHistory']);
            
    });

}); 