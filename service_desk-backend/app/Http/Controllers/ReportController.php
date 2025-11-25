<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReportController extends Controller
{
    public function generatePDF($id)
    {
        $report = Report::findOrFail($id);

        // Default values in case the fields are null
        $paperSize = $report->ukuran_kertas ?? 'A4';
        $orientation = strtolower($report->layout_kertas ?? 'portrait');

        // Execute the raw SQL query
        $rawQuery = (string) $report->query_report;
        $results = DB::select($rawQuery);

        // Get the current date and time for the "printed on" text and filename
        // Use a format that is friendly for filenames (e.g., replace colons with hyphens)
        $printDateTimeForDisplay = now()->format('Y-m-d H:i:s');
        $printDateTimeForFilename = now()->format('Ymd_His'); // Example: 20250716_094643

        // Generate HTML table from results
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . htmlspecialchars($report->nama_report) . '</title>
            <style>
                body {
                    font-family: "DejaVu Sans", sans-serif;
                    font-size: 10px;
                    margin: 40px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .header h2 {
                    margin-top: 0;
                    margin-bottom: 5px;
                    color: #333;
                }
                .print-info {
                    text-align: right;
                    font-size: 8px;
                    color: #777;
                    margin-bottom: 15px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 15px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f2f2f2;
                    font-weight: bold;
                    text-transform: uppercase;
                    font-size: 9px;
                }
                tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .footer {
                    position: fixed;
                    bottom: 20px;
                    left: 0;
                    right: 0;
                    text-align: center;
                    font-size: 8px;
                    color: #777;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>' . htmlspecialchars($report->nama_report) . '</h2>
            </div>
            <div class="print-info">
                This document was printed on ' . $printDateTimeForDisplay . '
            </div>
        ';

        $html .= "<table border='1' cellpadding='5' cellspacing='0'><thead><tr>";

        if (count($results)) {
            foreach (array_keys((array) $results[0]) as $column) {
                $html .= "<th>" . htmlspecialchars($column) . "</th>";
            }

            $html .= "</tr></thead><tbody>";
            foreach ($results as $row) {
                $html .= "<tr>";
                foreach ((array) $row as $value) {
                    $html .= "<td>" . htmlspecialchars($value) . "</td>";
                }
                $html .= "</tr>";
            }
            $html .= "</tbody></table>";
        } else {
            $html .= "<tr><td colspan='99' style='text-align: center; padding: 20px;'>No data found</td></tr></table>";
        }

        $html .= '
            <div class="footer">
                <script type="text/php">
                    if (isset($pdf)) {
                        $font = $fontMetrics->getFont("DejaVu Sans", "normal");
                        $size = 8;
                        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
                        $y = $pdf->get_height() - 24;
                        $x = $pdf->get_width() - $fontMetrics->getTextWidth($text, $font, $size) - 30;
                        $pdf->page_text($x, $y, $text, $font, $size, array(0,0,0));
                    }
                </script>
            </div>
        </body>
        </html>';

        // Set Dompdf options as an associative array
        $options = [
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ];

        // Load PDF with HTML and apply dynamic paper settings
        $pdf = Pdf::loadHTML($html)->setOptions($options);
        $pdf->setPaper($paperSize, $orientation);

        // Construct the filename with the report initial and the download date/time
        $filename = $report->nama_report . ' ' . $printDateTimeForFilename . '.pdf';

        return $pdf->download($filename);
    }


    public function generateExcel($id)
    {
        $report = Report::findOrFail($id);
        $rawQuery = (string) $report->query_report;
        $results = DB::select($rawQuery);

        $data = collect($results)->map(fn ($item) => (array) $item);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if ($data->isNotEmpty()) {
            $columns = array_keys($data->first());
            $startCol = 1; // Column B
            $startRow = 1; // Row 2
            $lastColIndex = $startCol + count($columns) - 1;
            $headerRange = Coordinate::stringFromColumnIndex($startCol) . $startRow . ':' . Coordinate::stringFromColumnIndex($lastColIndex) . $startRow;

            // Write header row
            foreach ($columns as $colIndex => $colName) {
                $colLetter = Coordinate::stringFromColumnIndex($startCol + $colIndex);
                $sheet->setCellValue($colLetter . $startRow, $colName);
            }

            // Write data rows starting from startRow + 1
            foreach ($data as $rowIndex => $row) {
                foreach (array_values($row) as $colIndex => $value) {
                    $colLetter = Coordinate::stringFromColumnIndex($startCol + $colIndex);
                    $sheet->setCellValue($colLetter . ($startRow + 1 + $rowIndex), $value);
                }
            }

            // Determine styling range
            $highestRow = $startRow + $data->count();
            $highestColLetter = Coordinate::stringFromColumnIndex($lastColIndex);
            $firstDataColLetter = Coordinate::stringFromColumnIndex($startCol); // 'B'
            $fullRange = "$firstDataColLetter$startRow:$highestColLetter$highestRow";

            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'], // black border for clarity
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true, // optional: ensures long content doesn't overflow
                ],
            ];

            $sheet->getStyle($fullRange)->applyFromArray($styleArray);

            // Header styling
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFEFEFEF'],
                ],
            ]);

            // Zebra striping
            for ($row = $startRow + 1; $row <= $highestRow; $row++) {
                if ($row % 2 === 0) {
                    $sheet->getStyle(Coordinate::stringFromColumnIndex($startCol) . $row . ':' . $highestColLetter . $row)
                        ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF9F9F9');
                }
            }

            // Auto-size only actual data columns
            for ($i = $startCol; $i <= $lastColIndex; $i++) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
            }

            $sheet->getColumnDimension('A')->setWidth(2);
        } else {
            $sheet->setCellValue('B2', 'No Data Found');
        }


        $writer = new Xlsx($spreadsheet);
        $fileName = $report->nama_report . ' ' . now()->format('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
    
    public function index(Request $request)
    {
        $query = Report::query();

        // ✅ Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_report', 'ilike', '%' . $request->search . '%')
                  ->orWhere('inisial_report', 'ilike', '%' . $request->search . '%')
                  ->orWhere('report_description', 'ilike', '%' . $request->search . '%');
            });
        }

        // ✅ Sorting
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir', 'asc'));
        $allowedSorts = ['id_report', 'nama_report', 'inisial_report', 'report_description', 'layout_kertas', 'ukuran_kertas'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('id_report', 'asc'); // default sort
        }

        // ✅ Pagination
        if ($request->has('page') || $request->has('per_page') || $request->has('search')) {
            $perPage = $request->input('per_page', 10);
            $reports = $query->paginate($perPage);

            return response()->json([
                'data' => $reports->items(),
                'total' => $reports->total(),
            ]);
        }

        return response()->json($query->get());
    }

    public function show($id)
    {
        return response()->json(Report::findOrFail($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_report'        => 'required|string|max:255',
            'inisial_report'     => 'required|string|max:100',
            'report_description' => 'nullable|string',
            'ukuran_kertas'      => 'nullable|string|max:10',
            'layout_kertas'      => 'nullable|string|max:20',
            'query_report'       => 'required|string',
        ]);

        $report = Report::create($validated);
        return response()->json($report, 201);
    }

    public function update(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        $validated = $request->validate([
            'nama_report'        => 'required|string|max:255',
            'inisial_report'     => 'required|string|max:100',
            'report_description' => 'nullable|string',
            'ukuran_kertas'      => 'nullable|string|max:10',
            'layout_kertas'      => 'nullable|string|max:20',
            'query_report'       => 'required|string',
        ]);

        $report->update($validated);
        return response()->json($report);
    }

    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
