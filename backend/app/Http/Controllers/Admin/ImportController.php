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
use Illuminate\Support\Facades\Cache;
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
    protected $checklistBatch = [];
    protected $batchSize = 100;
    
    // Cache arrays for faster lookups
    protected $existingCases = [];
    protected $existingClients = [];
    protected $existingDocs = [];
    protected $existingCourts = [];

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

        // Start timing
        $startTime = microtime(true);

        DB::beginTransaction();

        try {
            $spreadsheet = IOFactory::load($path);
            
            // Reset counters
            $this->resetCounters();
            
            // Cache existing data for faster lookups
            $this->cacheExistingData();
            
            // Process sheets
            $this->processMainSheet($spreadsheet->getSheetByName('Sheet1'), $categoryId, $lawyerId, $clerkId);
            
            // Flush any remaining checklist items
            $this->flushChecklistBatch();
            
            // Import courts if option is enabled
            if ($importCourts && !empty($this->uniqueCourts)) {
                $this->importCourts();
            }
            
            // Import documents
            if (!empty($this->uniqueDocuments)) {
                $this->importDocuments();
            }
            
            DB::commit();

            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);

            $message = $this->buildSuccessMessage() . " (Time: {$executionTime}s)";

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'cases_imported' => $this->caseCount,
                    'clients_created' => $this->clientCount,
                    'checklists_added' => $this->checklistCount,
                    'courts_added' => $this->courtCount,
                    'documents_added' => $this->documentCount,
                    'skipped' => $this->skippedCount,
                    'execution_time' => $executionTime
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

    /**
     * Cache existing data for faster lookups
     */
    private function cacheExistingData()
    {
        // Cache all existing case numbers
        $this->existingCases = Cases::pluck('id', 'case_no')->toArray();
        
        // Cache all existing client names
        $this->existingClients = Client::pluck('id', 'full_name')->toArray();
        
        // Cache all existing document types
        $this->existingDocs = Document::pluck('id', 'type')->toArray();
        
        // Cache all existing courts
        $this->existingCourts = Court::pluck('id', 'name')->toArray();
        
        \Log::info('Data cached: ' . count($this->existingCases) . ' cases, ' . 
                   count($this->existingClients) . ' clients, ' .
                   count($this->existingDocs) . ' documents');
    }

    /**
     * Reset all counters
     */
    private function resetCounters()
    {
        $this->caseCount = 0;
        $this->clientCount = 0;
        $this->checklistCount = 0;
        $this->courtCount = 0;
        $this->documentCount = 0;
        $this->skippedCount = 0;
        $this->uniqueCourts = [];
        $this->uniqueDocuments = [];
        $this->checklistBatch = [];
    }

    /**
     * Build success message
     */
    private function buildSuccessMessage()
    {
        $message = "Import completed! ";
        $message .= "{$this->caseCount} new cases, ";
        $message .= "{$this->clientCount} new clients, ";
        $message .= "{$this->checklistCount} checklist items";
        
        if ($this->courtCount > 0) {
            $message .= ", {$this->courtCount} new courts added";
        }
        
        if ($this->documentCount > 0) {
            $message .= ", {$this->documentCount} new document types added";
        }
        
        if ($this->skippedCount > 0) {
            $message .= ", {$this->skippedCount} existing cases updated with checklist items";
        }
        
        return $message;
    }

    /**
     * Convert any date format to MySQL date (YYYY-MM-DD)
     */
    private function convertToMySQLDate($dateString)
    {
        if (empty($dateString) || $dateString === '?' || $dateString === '') {
            return null;
        }
        
        // If it's already in YYYY-MM-DD format, return as is
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            return $dateString;
        }
        
        // If it's in YYYY-MM-DD HH:MM:SS format, extract date part
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $dateString)) {
            return substr($dateString, 0, 10);
        }
        
        // Remove ordinal suffixes (st, nd, rd, th)
        $cleaned = preg_replace('/(\d+)(st|nd|rd|th)/', '$1', $dateString);
        
        // Remove dots from month abbreviations (Oct. -> Oct)
        $cleaned = str_replace('.', '', $cleaned);
        
        // Try to parse with strtotime
        $timestamp = strtotime($cleaned);
        
        if ($timestamp !== false && $timestamp > 0) {
            return date('Y-m-d', $timestamp);
        }
        
        // Try with comma removal
        $cleaned = str_replace(',', '', $cleaned);
        $timestamp = strtotime($cleaned);
        
        if ($timestamp !== false && $timestamp > 0) {
            return date('Y-m-d', $timestamp);
        }
        
        // Try with different format: "October 23 2019" (no comma)
        $timestamp = strtotime(str_replace(',', '', $dateString));
        
        if ($timestamp !== false && $timestamp > 0) {
            return date('Y-m-d', $timestamp);
        }
        
        // Log unparseable dates
        \Log::warning("Could not parse date: {$dateString}");
        
        return null;
    }

    private function processMainSheet($sheet, $categoryId, $lawyerId, $clerkId)
    {
        if (!$sheet) return;
        
        $rows = $sheet->toArray();
        array_shift($rows); // Remove header
        
        $currentCase = null;
        $rowCount = 0;
        $totalRows = count($rows);
        
        \Log::info("Processing {$totalRows} rows...");
        
        foreach ($rows as $row) {
            $rowCount++;
            if ($rowCount % 1000 === 0) {
                \Log::info("Processed {$rowCount}/{$totalRows} rows...");
            }
            
            if (empty(array_filter($row))) continue;
            
            $no = $row[0] ?? null;
            $name = $row[1] ?? '';
            $contact = $row[2] ?? '';
            $address = $row[3] ?? '';
            $crimCaseNo = $row[4] ?? '';
            $crimDesc = $row[5] ?? '';
            $checklist = $row[6] ?? '';
            $date = $row[7] ?? null;
            $courtOffice = $row[8] ?? null;
            
            // Collect unique courts/offices
            if (!empty($courtOffice)) {
                $courtName = trim($courtOffice);
                if (!in_array($courtName, $this->uniqueCourts)) {
                    $this->uniqueCourts[] = $courtName;
                }
            }
            
            // Collect UNIQUE document types
            if (!empty($checklist)) {
                $docName = trim(str_replace(['<br>', '<br/>', "\n"], ' ', $checklist));
                $docName = $this->extractKeyword($docName);
                
                if (!empty($docName) && !in_array($docName, $this->uniqueDocuments)) {
                    $this->uniqueDocuments[] = $docName;
                }
            }
            
            // New case if we have a new number
            if (!empty($no) && is_numeric($no)) {
                // Check if case exists using cached data
                $caseId = $this->existingCases[(string)$no] ?? null;
                
                if ($caseId) {
                    // Case exists, get it for checklist items
                    $currentCase = Cases::find($caseId);
                    $this->skippedCount++;
                } else {
                    // Create new client (check cache first)
                    $clientId = $this->existingClients[trim($name)] ?? null;
                    
                    if (!$clientId) {
                        $client = Client::create([
                            'full_name' => trim($name),
                            'contact_no' => $contact ?: null,
                            'address' => $address ?: null,
                        ]);
                        $clientId = $client->id;
                        $this->existingClients[trim($name)] = $clientId;
                        $this->clientCount++;
                    }
                    
                    // Generate case code
                    $caseCode = $this->generateCaseCode();
                    
                    // Create new case
                    $currentCase = Cases::create([
                        'case_no' => (string)$no,
                        'case_code' => $caseCode,
                        'title' => $crimDesc ?: 'Criminal Case',
                        'category_id' => $categoryId,
                        'client_id' => $clientId,
                        'assigned_lawyer_id' => $lawyerId,
                        'assigned_clerk_id' => $clerkId ?: null,
                        'priority' => 'normal',
                        'case_status' => 'active',
                        'court_or_office' => $courtOffice ?: 'Regional Trial Court',
                        'docket_no' => $crimCaseNo,
                        'summary' => $crimDesc,
                        'created_by' => auth()->id(),
                    ]);
                    
                    // Add to cache
                    $this->existingCases[(string)$no] = $currentCase->id;
                    
                    $this->caseCount++;
                }
            }
            
            // Add checklist items for current case
            if ($currentCase && !empty($checklist)) {
                $this->addChecklistItemBatched($currentCase, $checklist, $date);
            }
        }
        
        \Log::info("Finished processing {$totalRows} rows");
    }

    /**
     * Add checklist item in batches for faster inserts
     */
    private function addChecklistItemBatched($case, $checklistName, $date)
    {
        $keyword = $this->extractKeyword($checklistName);
        
        // Convert date to MySQL format
        $mysqlDate = $this->convertToMySQLDate($date);
        
        // Find document ID from cache
        $documentId = null;
        
        // Check exact match
        if (isset($this->existingDocs[$keyword])) {
            $documentId = $this->existingDocs[$keyword];
        } else {
            // Try to find similar
            foreach ($this->existingDocs as $type => $id) {
                if (stripos($type, $keyword) !== false || stripos($keyword, $type) !== false) {
                    $documentId = $id;
                    break;
                }
            }
        }
        
        if (!$documentId) {
            // Document will be created later
            return;
        }
        
        // Add to batch
        $this->checklistBatch[] = [
            'case_id' => $case->id,
            'created_by' => auth()->id(),
            'document_type_id' => $documentId,
            'status' => 'done',
            'due_date' => $mysqlDate,
            'completed_at' => $mysqlDate, // Same as due_date for imported items
            'notes' => 'Imported from Excel',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $this->checklistCount++;
        
        // Flush batch if it reaches batch size
        if (count($this->checklistBatch) >= $this->batchSize) {
            $this->flushChecklistBatch();
        }
    }

    /**
     * Insert batch of checklist items
     */
    private function flushChecklistBatch()
    {
        if (empty($this->checklistBatch)) {
            return;
        }
        
        CaseChecklist::insert($this->checklistBatch);
        $this->checklistBatch = [];
    }

    /**
     * Import unique courts (OPTIMIZED)
     */
    private function importCourts()
    {
        $existingCourts = Court::pluck('id', 'name')->toArray();
        $maxSort = Court::where('sort_order', '<', 9000)->max('sort_order') ?? -1;
        $nextSort = $maxSort + 1;
        $courtsToCreate = [];
        
        foreach ($this->uniqueCourts as $courtName) {
            if (strtoupper($courtName) === 'OTHERS') {
                continue;
            }
            
            if (!isset($existingCourts[$courtName])) {
                $courtsToCreate[] = [
                    'name' => $courtName,
                    'type' => $this->determineCourtType($courtName),
                    'address' => null,
                    'contact_info' => null,
                    'is_active' => true,
                    'sort_order' => $nextSort++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        if (!empty($courtsToCreate)) {
            Court::insert($courtsToCreate);
            $this->courtCount = count($courtsToCreate);
        }
        
        $this->renumberCourtSortOrders();
    }

    /**
     * Import unique documents (OPTIMIZED)
     */
    private function importDocuments()
    {
        $maxSort = Document::where('sort_order', '<', 9000)->max('sort_order') ?? -1;
        $nextSort = $maxSort + 1;
        $docsToCreate = [];
        
        foreach ($this->uniqueDocuments as $docName) {
            if (strtoupper($docName) === 'OTHER' || strtoupper($docName) === 'OTHERS') {
                continue;
            }
            
            // Check if exists (case-insensitive)
            $exists = false;
            foreach ($this->existingDocs as $type => $id) {
                if (strtolower($type) === strtolower($docName)) {
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                $docsToCreate[] = [
                    'type' => $docName,
                    'category' => $this->categorizeChecklist($docName),
                    'color' => $this->getColorForChecklist($docName),
                    'requires_approval' => false,
                    'is_active' => true,
                    'sort_order' => $nextSort++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        if (!empty($docsToCreate)) {
            Document::insert($docsToCreate);
            $this->documentCount = count($docsToCreate);
        }
        
        $this->renumberDocumentSortOrders();
    }

    /**
     * Extract keyword from checklist string
     */
    private function extractKeyword($checklistString)
    {
        $upper = strtoupper($checklistString);
        
        $keywords = [
            'DEED OF SALE', 'DEED OF ABSOLUTE SALE', 'DEED OF ASSIGNMENT',
            'ORDER', 'DECISION', 'INFORMATION', 'AFFIDAVIT', 'MOTION', 
            'PETITION', 'BAIL', 'TRANSCRIPT', 'SUBPOENA', 'INVENTORY',
            'CHAIN OF CUSTODY', 'CHEMISTRY', 'JUDICIAL AFFIDAVIT',
            'CUSTODIAL INVESTIGATION', 'MEMORANDUM', 'CERTIFICATE',
            'COORDINATION', 'PRE-OPERATION', 'REFERRAL', 'CONSOLIDATION',
            'OMNIBUS', 'AMENDED', 'RESOLUTION', 'COMPLAINT', 'ANSWER',
            'REPLY', 'COMMENT', 'OPPOSITION', 'MANIFESTATION',
            'NOTICE', 'SUMMONS', 'WRIT', 'EXECUTION', 'SATISFACTION',
            'FORMAL OFFER OF DOCUMENTARY EVIDENCE', 'FORMAL OFFER',
            'RECONSIDERATION', 'MOTION FOR RECONSIDERATION',
            'CERTIORARI', 'MANDAMUS', 'PROHIBITION', 'QUO WARRANTO',
            'HABEAS CORPUS', 'AMICUS CURIAE', 'INTERVENTION'
        ];
        
        usort($keywords, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        foreach ($keywords as $keyword) {
            if (strpos($upper, $keyword) !== false) {
                return ucwords(strtolower($keyword));
            }
        }
        
        // Clean up the string
        $cleaned = preg_replace('/\d{4}-\d{2}-\d{2}/', '', $checklistString);
        $cleaned = preg_replace('/\b(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]* \d{1,2},? \d{4}\b/i', '', $cleaned);
        $cleaned = preg_replace('/[A-Z]+-\d+-\d+/', '', $cleaned);
        $cleaned = preg_replace('/\b\d{4}\b/', '', $cleaned);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        $cleaned = trim($cleaned);
        
        if (strlen($cleaned) > 50) {
            $cleaned = substr($cleaned, 0, 50) . '...';
        }
        
        return !empty($cleaned) ? $cleaned : 'Other';
    }

    /**
     * Renumber court sort orders
     */
    private function renumberCourtSortOrders()
    {
        $courts = Court::orderBy('sort_order')->get();
        $counter = 0;
        
        foreach ($courts as $court) {
            if ($court->name === 'Others') {
                $court->sort_order = 9999;
            } else {
                $court->sort_order = $counter;
                $counter++;
            }
            $court->save();
        }
    }

    /**
     * Renumber document sort orders
     */
    private function renumberDocumentSortOrders()
    {
        $normalDocs = Document::where('sort_order', '<', 9000)
            ->orderBy('sort_order')
            ->get();
        
        $counter = 0;
        
        foreach ($normalDocs as $doc) {
            $doc->sort_order = $counter;
            $doc->save();
            $counter++;
        }
        
        $other = Document::where('category', 'Other')
            ->orWhere('type', 'LIKE', '%Other%')
            ->orWhere('type', 'LIKE', '%Others%')
            ->first();
        
        if ($other) {
            $other->sort_order = 9999;
            $other->save();
        }
    }

    /**
     * Determine court type
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
     * Generate case code
     */
    private function generateCaseCode()
    {
        $year = date('Y');
        
        $lastCase = Cases::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastCase) {
            $lastCode = $lastCase->case_code;
            $parts = explode('-', $lastCode);
            $lastNumber = isset($parts[1]) ? intval($parts[1]) : 0;
            $sequence = $lastNumber + 1;
        } else {
            $sequence = 1;
        }
        
        return $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
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
        if (strpos($name, 'DEED') !== false) return 'Other';
        if (strpos($name, 'FORMAL OFFER') !== false) return 'Pleading';
        if (strpos($name, 'RECONSIDERATION') !== false) return 'Pleading';
        
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
            'DEED' => '#0f766e',
            'FORMAL OFFER' => '#b45309',
        ];
        
        foreach ($colors as $key => $color) {
            if (stripos($name, $key) !== false) {
                return $color;
            }
        }
        
        return '#94a3b8';
    }
}