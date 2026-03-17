<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Cases;
use App\Models\CaseChecklist;
use App\Models\Document;
use App\Models\Court;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    protected $caseCount = 0;
    protected $clientCount = 0;
    protected $checklistCount = 0;
    protected $courtCount = 0;
    protected $documentCount = 0;
    protected $skippedCount = 0;
    protected $uniqueCourts = [];
    protected $uniqueDocuments = [];

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
            'category_id' => 'required|exists:case_categories,id',
            'lawyer_id' => 'required|exists:users,id',
            'clerk_id' => 'nullable|exists:users,id',
            'import_courts' => 'nullable|in:0,1'
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $categoryId = $request->category_id;
        $lawyerId = $request->lawyer_id;
        $clerkId = $request->clerk_id;
        $importCourts = $request->import_courts === '1';

        DB::beginTransaction();

        try {
            $spreadsheet = IOFactory::load($path);
            
            // Reset counters
            $this->caseCount = 0;
            $this->clientCount = 0;
            $this->checklistCount = 0;
            $this->courtCount = 0;
            $this->documentCount = 0;
            $this->skippedCount = 0;
            $this->uniqueCourts = [];
            $this->uniqueDocuments = [];
            
            // Process sheets
            $this->processMainSheet($spreadsheet->getSheetByName('Sheet1'), $categoryId, $lawyerId, $clerkId);
            $this->processSheet2($spreadsheet->getSheetByName('Sheet2'), $categoryId, $lawyerId, $clerkId);
            
            // Import courts if option is enabled
            if ($importCourts && !empty($this->uniqueCourts)) {
                $this->importCourts();
            }
            
            // Import documents (always import unique document types)
            if (!empty($this->uniqueDocuments)) {
                $this->importDocuments();
            }
            
            DB::commit();

            $message = "Import completed! ";
            $message .= "{$this->caseCount} new cases, ";
            $message .= "{$this->clientCount} new clients, ";
            $message .= "{$this->checklistCount} checklist items";
            
            if ($this->courtCount > 0) {
                $message .= ", {$this->courtCount} courts added";
            }
            
            if ($this->documentCount > 0) {
                $message .= ", {$this->documentCount} document types added";
            }
            
            if ($this->skippedCount > 0) {
                $message .= ", {$this->skippedCount} cases skipped (duplicates)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'cases_imported' => $this->caseCount,
                    'clients_created' => $this->clientCount,
                    'checklists_added' => $this->checklistCount,
                    'courts_added' => $this->courtCount,
                    'documents_added' => $this->documentCount,
                    'skipped' => $this->skippedCount
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processMainSheet($sheet, $categoryId, $lawyerId, $clerkId)
    {
        if (!$sheet) return;
        
        $rows = $sheet->toArray();
        array_shift($rows); // Remove header
        
        $currentCase = null;
        
        foreach ($rows as $row) {
            if (empty(array_filter($row))) continue;
            
            $no = $row[0] ?? null;
            $name = $row[1] ?? '';
            $contact = $row[2] ?? '';
            $address = $row[3] ?? '';
            $crimCaseNo = $row[4] ?? '';
            $crimDesc = $row[5] ?? '';
            $checklist = $row[6] ?? '';
            $date = $row[7] ?? null;
            $courtOffice = $row[8] ?? null; // Court/Office column if exists
            
            // Collect unique courts/offices
            if (!empty($courtOffice)) {
                $courtName = trim($courtOffice);
                if (!in_array($courtName, $this->uniqueCourts)) {
                    $this->uniqueCourts[] = $courtName;
                }
            }
            
            // Collect unique document types from checklist
            if (!empty($checklist)) {
                $docName = trim(str_replace(['<br>', '<br/>', "\n"], ' ', $checklist));
                if (!in_array($docName, $this->uniqueDocuments)) {
                    $this->uniqueDocuments[] = $docName;
                }
            }
            
            // New case if we have a new number
            if (!empty($no) && is_numeric($no)) {
                // Check if case with this number already exists
                $existingCase = Cases::where('case_no', (string)$no)->first();
                
                if ($existingCase) {
                    // Case exists, use it for checklist items
                    $currentCase = $existingCase;
                    $this->skippedCount++;
                } else {
                    // Create new client
                    $client = Client::firstOrCreate(
                        ['full_name' => trim($name)],
                        [
                            'contact_no' => $contact ?: null,
                            'address' => $address ?: null,
                        ]
                    );
                    
                    if ($client->wasRecentlyCreated) {
                        $this->clientCount++;
                    }
                    
                    // Generate case code
                    $caseCode = $this->generateCaseCode();
                    
                    // Create new case with category, lawyer, and clerk
                    $currentCase = Cases::create([
                        'case_no' => (string)$no,
                        'case_code' => $caseCode,
                        'title' => $crimDesc ?: 'Criminal Case',
                        'category_id' => $categoryId,
                        'client_id' => $client->id,
                        'assigned_lawyer_id' => $lawyerId,
                        'assigned_clerk_id' => $clerkId ?: null,
                        'priority' => 'normal',
                        'case_status' => 'active',
                        'court_or_office' => $courtOffice ?: 'Regional Trial Court',
                        'docket_no' => $crimCaseNo,
                        'summary' => $crimDesc,
                        'created_by' => auth()->id(),
                    ]);
                    
                    $this->caseCount++;
                }
            }
            
            // Add checklist items for current case
            if ($currentCase && !empty($checklist)) {
                $this->addChecklistItem($currentCase, $checklist, $date);
            }
        }
    }

    private function processSheet2($sheet, $categoryId, $lawyerId, $clerkId)
    {
        if (!$sheet) return;
        
        $rows = $sheet->toArray();
        array_shift($rows);
        
        $currentCase = null;
        
        foreach ($rows as $row) {
            if (empty(array_filter($row))) continue;
            
            $no = $row[0] ?? null;
            $name = $row[1] ?? '';
            $contact = $row[2] ?? '';
            $address = $row[3] ?? '';
            $crimCaseNo = $row[4] ?? '';
            $crimDesc = $row[5] ?? '';
            $checklist = $row[6] ?? '';
            $date = $row[7] ?? null;
            $courtOffice = $row[8] ?? null; // Court/Office column if exists
            
            // Collect unique courts/offices
            if (!empty($courtOffice)) {
                $courtName = trim($courtOffice);
                if (!in_array($courtName, $this->uniqueCourts)) {
                    $this->uniqueCourts[] = $courtName;
                }
            }
            
            // Collect unique document types from checklist
            if (!empty($checklist)) {
                $docName = trim(str_replace(['<br>', '<br/>', "\n"], ' ', $checklist));
                if (!in_array($docName, $this->uniqueDocuments)) {
                    $this->uniqueDocuments[] = $docName;
                }
            }
            
            if (!empty($no) && is_numeric($no)) {
                // Check if case with this number already exists
                $existingCase = Cases::where('case_no', (string)$no)->first();
                
                if ($existingCase) {
                    $currentCase = $existingCase;
                    $this->skippedCount++;
                } else {
                    // Parse multiple names
                    $names = explode(',', $name);
                    $mainName = trim($names[0]);
                    
                    $client = Client::firstOrCreate(
                        ['full_name' => $mainName],
                        [
                            'contact_no' => $contact ?: null,
                            'address' => $address ?: null,
                        ]
                    );
                    
                    if ($client->wasRecentlyCreated) {
                        $this->clientCount++;
                    }
                    
                    $caseCode = $this->generateCaseCode();
                    
                    $currentCase = Cases::create([
                        'case_no' => (string)$no,
                        'case_code' => $caseCode,
                        'title' => $crimDesc ?: 'Criminal Case',
                        'category_id' => $categoryId,
                        'client_id' => $client->id,
                        'assigned_lawyer_id' => $lawyerId,
                        'assigned_clerk_id' => $clerkId ?: null,
                        'priority' => 'normal',
                        'case_status' => 'active',
                        'court_or_office' => $courtOffice ?: 'Regional Trial Court',
                        'docket_no' => $crimCaseNo,
                        'summary' => $crimDesc,
                        'created_by' => auth()->id(),
                    ]);
                    
                    $this->caseCount++;
                }
            }
            
            if ($currentCase && !empty($checklist)) {
                $this->addChecklistItem($currentCase, $checklist, $date);
            }
        }
    }

    /**
     * Import unique courts to Court Master (2nd to last position)
     */
    private function importCourts()
    {
        // Get all existing courts ordered by sort_order
        $existingCourts = Court::orderBy('sort_order')->get();
        
        // If no existing courts, just add at position 0
        if ($existingCourts->isEmpty()) {
            $sortOrder = 0;
            foreach ($this->uniqueCourts as $courtName) {
                $existingCourt = Court::where('name', $courtName)->first();
                if (!$existingCourt) {
                    Court::create([
                        'name' => $courtName,
                        'type' => $this->determineCourtType($courtName),
                        'address' => null,
                        'contact_info' => null,
                        'is_active' => true,
                        'sort_order' => $sortOrder++,
                    ]);
                    $this->courtCount++;
                }
            }
            return;
        }
        
        // Calculate the target position (second to last)
        $totalCourts = $existingCourts->count();
        $targetPosition = max(0, $totalCourts - 1); // Second to last means before the last item
        
        // Get the sort order at the target position
        if ($targetPosition < $totalCourts) {
            $targetCourt = $existingCourts[$targetPosition];
            $targetSort = $targetCourt->sort_order;
            
            // Shift all courts with sort_order > $targetSort up by the number of new courts
            $newCourtsCount = count($this->uniqueCourts);
            Court::where('sort_order', '>', $targetSort)
                ->increment('sort_order', $newCourtsCount);
            
            // Insert new courts starting at targetSort + 1
            $currentSort = $targetSort + 1;
            foreach ($this->uniqueCourts as $courtName) {
                $existingCourt = Court::where('name', $courtName)->first();
                if (!$existingCourt) {
                    Court::create([
                        'name' => $courtName,
                        'type' => $this->determineCourtType($courtName),
                        'address' => null,
                        'contact_info' => null,
                        'is_active' => true,
                        'sort_order' => $currentSort++,
                    ]);
                    $this->courtCount++;
                }
            }
        } else {
            // If something went wrong, add at the end
            $maxSort = Court::max('sort_order') ?? 0;
            $currentSort = $maxSort + 1;
            
            foreach ($this->uniqueCourts as $courtName) {
                $existingCourt = Court::where('name', $courtName)->first();
                if (!$existingCourt) {
                    Court::create([
                        'name' => $courtName,
                        'type' => $this->determineCourtType($courtName),
                        'address' => null,
                        'contact_info' => null,
                        'is_active' => true,
                        'sort_order' => $currentSort++,
                    ]);
                    $this->courtCount++;
                }
            }
        }
    }

    /**
     * Import unique document types to Document Master (2nd to last position)
     */
    private function importDocuments()
    {
        // Get all existing documents ordered by sort_order
        $existingDocs = Document::orderBy('sort_order')->get();
        
        // If no existing documents, just add at position 0
        if ($existingDocs->isEmpty()) {
            $sortOrder = 0;
            foreach ($this->uniqueDocuments as $docName) {
                $existingDoc = Document::where('type', $docName)->first();
                if (!$existingDoc) {
                    Document::create([
                        'type' => $docName,
                        'category' => $this->categorizeChecklist($docName),
                        'color' => $this->getColorForChecklist($docName),
                        'requires_approval' => false,
                        'is_active' => true,
                        'sort_order' => $sortOrder++,
                    ]);
                    $this->documentCount++;
                }
            }
            return;
        }
        
        // Calculate the target position (second to last)
        $totalDocs = $existingDocs->count();
        $targetPosition = max(0, $totalDocs - 1); // Second to last means before the last item
        
        // Get the sort order at the target position
        if ($targetPosition < $totalDocs) {
            $targetDoc = $existingDocs[$targetPosition];
            $targetSort = $targetDoc->sort_order;
            
            // Shift all documents with sort_order > $targetSort up by the number of new documents
            $newDocsCount = count($this->uniqueDocuments);
            Document::where('sort_order', '>', $targetSort)
                ->increment('sort_order', $newDocsCount);
            
            // Insert new documents starting at targetSort + 1
            $currentSort = $targetSort + 1;
            foreach ($this->uniqueDocuments as $docName) {
                $existingDoc = Document::where('type', $docName)->first();
                if (!$existingDoc) {
                    Document::create([
                        'type' => $docName,
                        'category' => $this->categorizeChecklist($docName),
                        'color' => $this->getColorForChecklist($docName),
                        'requires_approval' => false,
                        'is_active' => true,
                        'sort_order' => $currentSort++,
                    ]);
                    $this->documentCount++;
                }
            }
        } else {
            // If something went wrong, add at the end
            $maxSort = Document::max('sort_order') ?? 0;
            $currentSort = $maxSort + 1;
            
            foreach ($this->uniqueDocuments as $docName) {
                $existingDoc = Document::where('type', $docName)->first();
                if (!$existingDoc) {
                    Document::create([
                        'type' => $docName,
                        'category' => $this->categorizeChecklist($docName),
                        'color' => $this->getColorForChecklist($docName),
                        'requires_approval' => false,
                        'is_active' => true,
                        'sort_order' => $currentSort++,
                    ]);
                    $this->documentCount++;
                }
            }
        }
    }

    /**
     * Determine court type based on name
     */
    private function determineCourtType($name)
    {
        $name = strtoupper($name);
        
        if (strpos($name, 'RTC') !== false) return 'Regional Trial Court';
        if (strpos($name, 'MTC') !== false) return 'Municipal Trial Court';
        if (strpos($name, 'METC') !== false) return 'Metropolitan Trial Court';
        if (strpos($name, 'CA') !== false) return 'Court of Appeals';
        if (strpos($name, 'SC') !== false) return 'Supreme Court';
        if (strpos($name, 'PROSECUTOR') !== false) return 'Prosecutor\'s Office';
        if (strpos($name, 'PAO') !== false) return 'Public Attorney\'s Office';
        
        return 'Other';
    }

    /**
     * Generate case code in same format as CaseMaster (YEAR-0001)
     */
    private function generateCaseCode()
    {
        $year = date('Y');
        
        // Get the last case created this year
        $lastCase = Cases::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastCase) {
            // Extract the sequence number from the last case code
            $lastCode = $lastCase->case_code;
            $parts = explode('-', $lastCode);
            $lastNumber = isset($parts[1]) ? intval($parts[1]) : 0;
            $sequence = $lastNumber + 1;
        } else {
            $sequence = 1;
        }
        
        return $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    private function addChecklistItem($case, $checklistName, $date)
    {
        $checklistName = trim(str_replace(['<br>', '<br/>', "\n"], ' ', $checklistName));
        
        // Find or create document
        $document = Document::where('type', $checklistName)->first();
        
        if (!$document) {
            // This shouldn't happen because we import documents first
            // But just in case, create it
            $document = Document::create([
                'type' => $checklistName,
                'category' => $this->categorizeChecklist($checklistName),
                'color' => $this->getColorForChecklist($checklistName),
                'requires_approval' => false,
                'is_active' => true,
                'sort_order' => 0,
            ]);
        }
        
        // Check if this checklist item already exists for this case
        $existingItem = CaseChecklist::where('case_id', $case->id)
            ->where('document_type_id', $document->id)
            ->whereDate('due_date', $date ? date('Y-m-d', strtotime($date)) : null)
            ->first();
        
        if (!$existingItem) {
            CaseChecklist::create([
                'case_id' => $case->id,
                'created_by' => auth()->id(),
                'document_type_id' => $document->id,
                'status' => 'done',
                'due_date' => $date ? date('Y-m-d', strtotime($date)) : null,
                'completed_at' => $date,
                'notes' => 'Imported from Excel',
            ]);
            
            $this->checklistCount++;
        }
    }

    private function categorizeChecklist($name)
    {
        $name = strtoupper($name);
        
        if (strpos($name, 'ORDER') !== false) return 'Court Issuance';
        if (strpos($name, 'DECISION') !== false) return 'Court Issuance';
        if (strpos($name, 'INFORMATION') !== false) return 'Pleading';
        if (strpos($name, 'AFFIDAVIT') !== false) return 'Evidence';
        if (strpos($name, 'MOTION') !== false) return 'Pleading';
        if (strpos($name, 'PETITION') !== false) return 'Pleading';
        if (strpos($name, 'BAIL') !== false) return 'Pleading';
        if (strpos($name, 'TRANSCRIPT') !== false) return 'Court Issuance';
        if (strpos($name, 'SUBPOENA') !== false) return 'Court Issuance';
        if (strpos($name, 'INVENTORY') !== false) return 'Evidence';
        if (strpos($name, 'CHAIN OF CUSTODY') !== false) return 'Evidence';
        if (strpos($name, 'CHEMISTRY') !== false) return 'Evidence';
        
        return 'Other';
    }

    private function getColorForChecklist($name)
    {
        $colors = [
            'ORDER' => '#1a4972',
            'DECISION' => '#2d6db5',
            'INFORMATION' => '#10b981',
            'AFFIDAVIT' => '#f59e0b',
            'MOTION' => '#8b5cf6',
            'PETITION' => '#ec4899',
            'BAIL' => '#14b8a6',
            'TRANSCRIPT' => '#6b7280',
            'SUBPOENA' => '#ef4444',
            'INVENTORY' => '#f97316',
            'CHAIN' => '#8b5cf6',
            'CHEMISTRY' => '#06b6d4',
        ];
        
        foreach ($colors as $key => $color) {
            if (stripos($name, $key) !== false) {
                return $color;
            }
        }
        
        return '#94a3b8';
    }
}