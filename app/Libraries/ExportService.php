<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

/** Layanan export Excel/PDF sederhana untuk laporan. */
class ExportService
{
    /**
     * @param array $headers Contoh: ['Tanggal', 'Pemesan', 'Ruang', 'Status']
     * @param array $rows    Contoh: [['2025-09-01','Budi','Ruang Rapat 1','approved'], ...]
     */
    public function exportExcel(string $filename, array $headers, array $rows)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 1, $header);
            $col++;
        }

        // Rows
        $rowIdx = 2;
        foreach ($rows as $row) {
            $col = 1;
            foreach ($row as $cell) {
                $sheet->setCellValueByColumnAndRow($col, $rowIdx, $cell);
                $col++;
            }
            $rowIdx++;
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $excelOutput = ob_get_clean();

        return service('response')
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($excelOutput);
    }

    /**
     * @param string $title  Judul di PDF
     * @param array $headers Array string header kolom
     * @param array $rows    Array data 2D
     */
    public function exportPdf(string $filename, string $title, array $headers, array $rows)
    {
        $html = '<html><head><style>
            body{font-family: DejaVu Sans, Arial, sans-serif; font-size:12px;}
            h2{margin:0 0 10px 0;}
            table{border-collapse:collapse; width:100%;}
            th,td{border:1px solid #999; padding:6px;}
            th{background:#f0f0f0;}
        </style></head><body>';
        $html .= '<h2>' . htmlspecialchars($title) . '</h2>';
        $html .= '<table><thead><tr>';
        foreach ($headers as $h) {
            $html .= '<th>' . htmlspecialchars($h) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $r) {
            $html .= '<tr>';
            foreach ($r as $c) {
                $html .= '<td>' . htmlspecialchars((string)$c) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></body></html>';

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfOutput = $dompdf->output();

        return service('response')
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($pdfOutput);
    }
}