<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class CommissionLedgerReportExport implements FromView, ShouldAutoSize, WithColumnWidths
{
    use Exportable;

    public function __construct(private readonly array $data)
    {
    }

    public function view(): View
    {
        return view('file-exports.commission-ledger-report-export', [
            'data' => $this->data,
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 28,
            'C' => 18,
            'D' => 28,
            'E' => 22,
            'F' => 22,
            'G' => 20,
            'H' => 22,
            'I' => 24,
        ];
    }
}
