<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\Client;
use App\Models\User;
use App\Models\CaseCategory;
use App\Models\Document;
use App\Models\CaseChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use ZipArchive;

class ExportController extends Controller
{
    /**
     * Export cases in the exact format for re-import
     */
    public function exportCases(Request $request)
    {
        $format = $request->get('format', 'excel');
        $categoryId = $request->get('category_id');
        $groupByCategory = $request->boolean('group_by_category', false);
        
        // Build query
        $query = Cases::with([
            'client',
            'category',
            'checklists.document',
            'lawyer',
            'clerk',
            'currentStage'
        ]);
        
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        $cases = $query->get();
        
        if ($cases->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No cases found to export'
            ], 404);
        }
        
        // Handle different formats
        switch ($format) {
            case 'pdf':
                return $this->exportCasesToPdf($cases, $categoryId);
            case 'excel':
            default:
                if ($groupByCategory) {
                    return $this->exportGroupedByCategory($cases);
                }
                return $this->createExportFile($cases, 'cases');
        }
    }
    
    /**
     * Export cases to PDF
     */
    private function exportCasesToPdf($cases, $categoryId = null)
    {
        $categoryName = 'All Categories';
        if ($categoryId) {
            $category = CaseCategory::find($categoryId);
            $categoryName = $category ? $category->name : 'Selected Category';
        }
        
        $data = [
            'cases' => $cases,
            'category_name' => $categoryName,
            'export_date' => now()->format('F d, Y'),
            'total_cases' => $cases->count()
        ];
        
        $pdf = Pdf::loadView('exports.cases-pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        
        $filename = $categoryId 
            ? strtolower(str_replace(' ', '_', $categoryName)) . '_cases_' . date('Y-m-d') . '.pdf'
            : 'all_cases_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Export cases grouped by category
     */
    private function exportGroupedByCategory($cases)
    {
        $grouped = $cases->groupBy(function($case) {
            return $case->category?->name ?? 'Uncategorized';
        });
        
        $zip = new ZipArchive();
        $zipFileName = 'cases_by_category_' . date('Y-m-d_His') . '.zip';
        $zipPath = sys_get_temp_dir() . '/' . $zipFileName;
        
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            throw new \Exception('Cannot create zip file');
        }
        
        foreach ($grouped as $categoryName => $categoryCases) {
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $categoryName);
            $fileName = $safeName . '_cases.xlsx';
            
            $tempFile = tempnam(sys_get_temp_dir(), 'export_');
            $this->createExcelFile($categoryCases, $tempFile);
            
            $zip->addFile($tempFile, $fileName);
        }
        
        $zip->close();
        
        // Clean up temp files
        foreach ($grouped as $categoryCases) {
            // Files are cleaned up automatically when script ends
        }
        
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
    
    /**
     * Create export file
     */
    private function createExportFile($cases, $filename)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'export_');
        $this->createExcelFile($cases, $tempFile);
        
        return response()->download($tempFile, $filename . '_' . date('Y-m-d') . '.xlsx')
            ->deleteFileAfterSend(true);
    }
    
    /**
     * Create Excel file matching your import format
     */
    private function createExcelFile($cases, $filePath)
    {
        $spreadsheet = new Spreadsheet();
        
        // ===== SHEET 1: Main Sheet (Sheet1) =====
        $this->createMainSheet($spreadsheet, $cases);
        
        // ===== SHEET 2: Clients Sheet =====
        $this->createClientsSheet($spreadsheet, $cases);
        
        // ===== SHEET 3: Criminal Case Sheet =====
        $this->createCriminalCaseSheet($spreadsheet, $cases);
        
        // ===== SHEET 4: Checklist Sheet =====
        $this->createChecklistSheet($spreadsheet);
        
        // ===== SHEET 5: ChecklistDates Sheet =====
        $this->createChecklistDatesSheet($spreadsheet, $cases);
        
        // Save file
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        
        // Free memory
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }
    
    /**
     * Create Main Sheet (Sheet1)
     */
    private function createMainSheet($spreadsheet, $cases)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet1');
        
        // Headers
        $headers = ['No.', 'Name', 'Contact', 'Address', 'Crim. Case No.', 'Crim. Case Desc.', 'Checklist', 'Date'];
        $columnLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        
        foreach ($headers as $index => $header) {
            $cell = $columnLetters[$index] . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF1a4972');
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
        }
        
        // Data
        $row = 2;
        foreach ($cases as $case) {
            $checklists = $case->checklists ?? collect();
            $startRow = $row;
            
            if ($checklists->isEmpty()) {
                // Single row for case with no checklists
                $sheet->setCellValue('A' . $row, $case->case_no);
                $sheet->setCellValue('B' . $row, $case->client?->full_name ?? '');
                $sheet->setCellValue('C' . $row, $case->client?->contact_no ?? '');
                $sheet->setCellValue('D' . $row, $case->client?->address ?? '');
                $sheet->setCellValue('E' . $row, $case->docket_no ?? '');
                $sheet->setCellValue('F' . $row, $case->title ?? '');
                $sheet->setCellValue('G' . $row, '');
                $sheet->setCellValue('H' . $row, '');
                $row++;
            } else {
                // Multiple rows for checklists
                foreach ($checklists as $index => $checklist) {
                    $currentRow = $row + $index;
                    
                    // Case info only on first row
                    if ($index == 0) {
                        $sheet->setCellValue('A' . $currentRow, $case->case_no);
                        $sheet->setCellValue('B' . $currentRow, $case->client?->full_name ?? '');
                        $sheet->setCellValue('C' . $currentRow, $case->client?->contact_no ?? '');
                        $sheet->setCellValue('D' . $currentRow, $case->client?->address ?? '');
                    }
                    
                    // Criminal case info (same for all rows of this case)
                    $sheet->setCellValue('E' . $currentRow, $case->docket_no ?? '');
                    $sheet->setCellValue('F' . $currentRow, $case->title ?? '');
                    
                    // Checklist info
                    $sheet->setCellValue('G' . $currentRow, $checklist->document?->type ?? '');
                    $sheet->setCellValue('H' . $currentRow, $checklist->created_at?->format('Y-m-d H:i:s') ?? '');
                }
                
                // Merge case info cells if multiple rows
                if ($checklists->count() > 1) {
                    $lastRow = $row + $checklists->count() - 1;
                    $sheet->mergeCells('A' . $row . ':A' . $lastRow);
                    $sheet->mergeCells('B' . $row . ':B' . $lastRow);
                    $sheet->mergeCells('C' . $row . ':C' . $lastRow);
                    $sheet->mergeCells('D' . $row . ':D' . $lastRow);
                }
                
                $row += $checklists->count();
            }
            
            // Add empty row between cases (like in your sample)
            $row++;
        }
        
        // Auto-size columns
        foreach ($columnLetters as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    /**
     * Create Clients Sheet
     */
    private function createClientsSheet($spreadsheet, $cases)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Clients');
        
        // Headers
        $headers = ['case_id', 'case_no', 'name', 'address', 'contact'];
        $columnLetters = ['A', 'B', 'C', 'D', 'E'];
        
        foreach ($headers as $index => $header) {
            $cell = $columnLetters[$index] . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }
        
        // Data
        $row = 2;
        foreach ($cases as $case) {
            $sheet->setCellValue('A' . $row, $case->id);
            $sheet->setCellValue('B' . $row, $case->case_no);
            $sheet->setCellValue('C' . $row, $case->client?->full_name ?? '');
            $sheet->setCellValue('D' . $row, $case->client?->address ?? '');
            $sheet->setCellValue('E' . $row, $case->client?->contact_no ?? '');
            $row++;
        }
        
        // Auto-size columns
        foreach ($columnLetters as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    /**
     * Create Criminal Case Sheet
     */
    private function createCriminalCaseSheet($spreadsheet, $cases)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Criminal Case');
        
        // Headers
        $headers = ['criminal_id', 'case_id', 'criminal_case_no', 'description'];
        $columnLetters = ['A', 'B', 'C', 'D'];
        
        foreach ($headers as $index => $header) {
            $cell = $columnLetters[$index] . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }
        
        // Data
        $row = 2;
        $criminalId = 1;
        foreach ($cases as $case) {
            $sheet->setCellValue('A' . $row, $criminalId++);
            $sheet->setCellValue('B' . $row, $case->id);
            $sheet->setCellValue('C' . $row, $case->docket_no ?? '');
            $sheet->setCellValue('D' . $row, $case->title ?? '');
            $row++;
        }
        
        // Auto-size columns
        foreach ($columnLetters as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    /**
     * Create Checklist Sheet
     */
    private function createChecklistSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Checklist');
        
        // Headers
        $headers = ['checklist_id', 'checklist_name'];
        $columnLetters = ['A', 'B'];
        
        foreach ($headers as $index => $header) {
            $cell = $columnLetters[$index] . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }
        
        // Get all active documents as checklists
        $documents = Document::where('is_active', true)->orderBy('type')->get();
        
        // Data
        $row = 2;
        foreach ($documents as $doc) {
            $sheet->setCellValue('A' . $row, $doc->id);
            $sheet->setCellValue('B' . $row, $doc->type);
            $row++;
        }
        
        // Auto-size columns
        foreach ($columnLetters as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    /**
     * Create ChecklistDates Sheet
     */
    private function createChecklistDatesSheet($spreadsheet, $cases)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ChecklistDates');
        
        // Headers
        $headers = ['id', 'case_id', 'checklist_id', 'date'];
        $columnLetters = ['A', 'B', 'C', 'D'];
        
        foreach ($headers as $index => $header) {
            $cell = $columnLetters[$index] . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }
        
        // Data
        $row = 2;
        $dateId = 1;
        foreach ($cases as $case) {
            foreach ($case->checklists as $checklist) {
                $sheet->setCellValue('A' . $row, $dateId++);
                $sheet->setCellValue('B' . $row, $case->id);
                $sheet->setCellValue('C' . $row, $checklist->document_id);
                $sheet->setCellValue('D' . $row, $checklist->created_at?->format('Y-m-d H:i:s') ?? '');
                $row++;
            }
        }
        
        // Auto-size columns
        foreach ($columnLetters as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    /**
     * Export all data (full backup) — admin only
     */
    public function exportAll(Request $request)
    {
        // ── ONLY ADDITION: block non-admins ──────────────────────────────
        $role = strtolower(Auth::user()?->role?->name ?? Auth::user()?->role ?? '');
        if ($role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Full backup export is restricted to administrators only.',
            ], 403);
        }
        // ─────────────────────────────────────────────────────────────────

        $format = $request->get('format', 'excel');
        $groupByCategory = $request->boolean('group_by_category', false);
        
        $data = [
            'users' => User::with('role')->get(),
            'clients' => Client::all(),
            'categories' => CaseCategory::all(),
            'documents' => Document::all(),
            'cases' => Cases::with([
                'client',
                'category',
                'checklists.document',
                'lawyer',
                'clerk',
                'currentStage'
            ])->get(),
        ];
        
        if ($format === 'pdf') {
            return $this->exportAllToPdf($data);
        }
        
        if ($groupByCategory) {
            return $this->exportAllGroupedByCategory($data);
        }
        
        return $this->createFullBackupExcel($data);
    }
    
    /**
     * Export all to PDF
     */
    private function exportAllToPdf($data)
    {
        $pdf = Pdf::loadView('exports.full-backup-pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('full_backup_' . date('Y-m-d') . '.pdf');
    }
    
    /**
     * Export all data grouped by category
     */
    private function exportAllGroupedByCategory($data)
    {
        $grouped = $data['cases']->groupBy(function($case) {
            return $case->category?->name ?? 'Uncategorized';
        });
        
        $zip = new ZipArchive();
        $zipFileName = 'full_backup_by_category_' . date('Y-m-d_His') . '.zip';
        $zipPath = sys_get_temp_dir() . '/' . $zipFileName;
        
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            throw new \Exception('Cannot create zip file');
        }
        
        // Create master data file
        $masterFile = tempnam(sys_get_temp_dir(), 'master_');
        $this->createMasterDataExcel($masterFile, $data);
        $zip->addFile($masterFile, 'master_data.xlsx');
        
        // Create category files
        foreach ($grouped as $categoryName => $categoryCases) {
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $categoryName);
            $fileName = $safeName . '_cases.xlsx';
            
            $tempFile = tempnam(sys_get_temp_dir(), 'cat_');
            $this->createExcelFile($categoryCases, $tempFile);
            
            $zip->addFile($tempFile, $fileName);
        }
        
        $zip->close();
        
        // Clean up
        unlink($masterFile);
        
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
    
    /**
     * Create master data Excel file
     */
    private function createMasterDataExcel($filePath, $data)
    {
        $spreadsheet = new Spreadsheet();
        
        // Users Sheet
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Users');
        
        $headers = ['ID', 'Full Name', 'Email', 'Role', 'Status'];
        foreach ($headers as $index => $header) {
            $cell = chr(65 + $index) . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }
        
        $row = 2;
        foreach ($data['users'] as $user) {
            $sheet->setCellValue('A' . $row, $user->id);
            $sheet->setCellValue('B' . $row, $user->full_name);
            $sheet->setCellValue('C' . $row, $user->email);
            $sheet->setCellValue('D' . $row, $user->role?->name ?? '');
            $sheet->setCellValue('E' . $row, $user->status);
            $row++;
        }
        
        // Clients Sheet
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Clients');
        $this->createClientsSheet($spreadsheet, $data['cases']);
        
        // Categories Sheet
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Categories');
        
        $catHeaders = ['ID', 'Name', 'Color', 'Is Active'];
        foreach ($catHeaders as $index => $header) {
            $cell = chr(65 + $index) . '1';
            $sheet3->setCellValue($cell, $header);
            $sheet3->getStyle($cell)->getFont()->setBold(true);
        }
        
        $row = 2;
        foreach ($data['categories'] as $cat) {
            $sheet3->setCellValue('A' . $row, $cat->id);
            $sheet3->setCellValue('B' . $row, $cat->name);
            $sheet3->setCellValue('C' . $row, $cat->color);
            $sheet3->setCellValue('D' . $row, $cat->is_active ? 'Yes' : 'No');
            $row++;
        }
        
        // Documents Sheet
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('Documents');
        
        $docHeaders = ['ID', 'Type', 'Category', 'Is Active'];
        foreach ($docHeaders as $index => $header) {
            $cell = chr(65 + $index) . '1';
            $sheet4->setCellValue($cell, $header);
            $sheet4->getStyle($cell)->getFont()->setBold(true);
        }
        
        $row = 2;
        foreach ($data['documents'] as $doc) {
            $sheet4->setCellValue('A' . $row, $doc->id);
            $sheet4->setCellValue('B' . $row, $doc->type);
            $sheet4->setCellValue('C' . $row, $doc->category);
            $sheet4->setCellValue('D' . $row, $doc->is_active ? 'Yes' : 'No');
            $row++;
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }
    
    /**
     * Create full backup Excel file
     */
    private function createFullBackupExcel($data)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'backup_');
        
        $spreadsheet = new Spreadsheet();
        
        // Sheet1 - Main Cases
        $this->createMainSheet($spreadsheet, $data['cases']);
        
        // Clients Sheet
        $this->createClientsSheet($spreadsheet, $data['cases']);
        
        // Criminal Case Sheet
        $this->createCriminalCaseSheet($spreadsheet, $data['cases']);
        
        // Checklist Sheet
        $this->createChecklistSheet($spreadsheet);
        
        // ChecklistDates Sheet
        $this->createChecklistDatesSheet($spreadsheet, $data['cases']);
        
        // Users Sheet
        $sheet6 = $spreadsheet->createSheet();
        $sheet6->setTitle('Users');
        
        $headers = ['ID', 'Full Name', 'Email', 'Role', 'Status'];
        foreach ($headers as $index => $header) {
            $cell = chr(65 + $index) . '1';
            $sheet6->setCellValue($cell, $header);
            $sheet6->getStyle($cell)->getFont()->setBold(true);
        }
        
        $row = 2;
        foreach ($data['users'] as $user) {
            $sheet6->setCellValue('A' . $row, $user->id);
            $sheet6->setCellValue('B' . $row, $user->full_name);
            $sheet6->setCellValue('C' . $row, $user->email);
            $sheet6->setCellValue('D' . $row, $user->role?->name ?? '');
            $sheet6->setCellValue('E' . $row, $user->status);
            $row++;
        }
        
        // Categories Sheet
        $sheet7 = $spreadsheet->createSheet();
        $sheet7->setTitle('Categories');
        
        $catHeaders = ['ID', 'Name', 'Color', 'Is Active'];
        foreach ($catHeaders as $index => $header) {
            $cell = chr(65 + $index) . '1';
            $sheet7->setCellValue($cell, $header);
            $sheet7->getStyle($cell)->getFont()->setBold(true);
        }
        
        $row = 2;
        foreach ($data['categories'] as $cat) {
            $sheet7->setCellValue('A' . $row, $cat->id);
            $sheet7->setCellValue('B' . $row, $cat->name);
            $sheet7->setCellValue('C' . $row, $cat->color);
            $sheet7->setCellValue('D' . $row, $cat->is_active ? 'Yes' : 'No');
            $row++;
        }
        
        // Documents Sheet
        $sheet8 = $spreadsheet->createSheet();
        $sheet8->setTitle('Documents');
        
        $docHeaders = ['ID', 'Type', 'Category', 'Is Active'];
        foreach ($docHeaders as $index => $header) {
            $cell = chr(65 + $index) . '1';
            $sheet8->setCellValue($cell, $header);
            $sheet8->getStyle($cell)->getFont()->setBold(true);
        }
        
        $row = 2;
        foreach ($data['documents'] as $doc) {
            $sheet8->setCellValue('A' . $row, $doc->id);
            $sheet8->setCellValue('B' . $row, $doc->type);
            $sheet8->setCellValue('C' . $row, $doc->category);
            $sheet8->setCellValue('D' . $row, $doc->is_active ? 'Yes' : 'No');
            $row++;
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);
        
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        
        return response()->download($tempFile, 'full_backup_' . date('Y-m-d') . '.xlsx')
            ->deleteFileAfterSend(true);
    }
}