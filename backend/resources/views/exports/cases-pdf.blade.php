<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cases Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 10px;
        }
        .header {
            background-color: #1a4972;
            color: white;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 11px;
            opacity: 0.9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
        }
        th {
            background-color: #1a4972;
            color: white;
            padding: 6px 3px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 4px 3px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .case-header td {
            background-color: #e6f0fa;
            font-weight: bold;
        }
        .section-title {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 11px;
            padding: 8px;
            margin: 15px 0 5px;
        }
        .total {
            font-weight: bold;
            color: #1a4972;
            margin-top: 10px;
            padding: 5px;
            border-top: 2px solid #1a4972;
        }
        .page-break {
            page-break-after: always;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-urgent { background-color: #ef4444; color: white; }
        .badge-high { background-color: #f97316; color: white; }
        .badge-normal { background-color: #10b981; color: white; }
        .badge-low { background-color: #6b7280; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Cases Export Report</h1>
        <p>Category: {{ $category_name }} | Generated: {{ $export_date }} | Total Cases: {{ $total_cases }}</p>
    </div>

    @foreach($cases as $case)
    <div style="margin-bottom: 20px; page-break-inside: avoid;">
        <table>
            <tr class="case-header">
                <th colspan="4">Case #{{ $case->case_no }}: {{ $case->title }}</th>
                <th colspan="2" style="text-align: right;">
                    <span class="badge badge-{{ $case->priority }}">{{ ucfirst($case->priority) }}</span>
                </th>
            </tr>
            <tr>
                <td><strong>Client:</strong> {{ $case->client?->full_name ?? 'N/A' }}</td>
                <td><strong>Contact:</strong> {{ $case->client?->contact_no ?? 'N/A' }}</td>
                <td><strong>Lawyer:</strong> {{ $case->lawyer?->full_name ?? 'N/A' }}</td>
                <td><strong>Clerk:</strong> {{ $case->clerk?->full_name ?? 'N/A' }}</td>
                <td><strong>Stage:</strong> {{ $case->currentStage?->name ?? 'N/A' }}</td>
                <td><strong>Status:</strong> {{ ucfirst($case->case_status) }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Address:</strong> {{ $case->client?->address ?? 'N/A' }}</td>
                <td colspan="2"><strong>Docket No.:</strong> {{ $case->docket_no ?? 'N/A' }}</td>
                <td colspan="2"><strong>Court/Office:</strong> {{ $case->court_or_office ?? 'N/A' }}</td>
            </tr>
        </table>

        @if($case->checklists->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="25%">Checklist Item</th>
                    <th width="15%">Status</th>
                    <th width="15%">Due Date</th>
                    <th width="20%">Assigned To</th>
                    <th width="20%">Completed At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($case->checklists as $index => $checklist)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $checklist->document?->type ?? 'N/A' }}</td>
                    <td>
                        <span class="badge" style="background-color: 
                            @if($checklist->status == 'done') #10b981
                            @elseif($checklist->status == 'in-progress') #f59e0b
                            @else #6b7280
                            @endif; color: white;">
                            {{ ucfirst($checklist->status) }}
                        </span>
                    </td>
                    <td>{{ $checklist->due_date?->format('Y-m-d') ?? 'N/A' }}</td>
                    <td>{{ $checklist->assigned_to ?? 'N/A' }}</td>
                    <td>{{ $checklist->completed_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align: center; padding: 10px; background: #f9f9f9;">No checklist items for this case</p>
        @endif
    </div>
    
    @if(!$loop->last)
    <div style="border-top: 1px dashed #ccc; margin: 15px 0;"></div>
    @endif
    @endforeach

    <div class="total">
        Total Cases: {{ $total_cases }} | Generated on: {{ $export_date }}
    </div>
</body>
</html>