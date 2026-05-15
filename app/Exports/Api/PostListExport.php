<?php

namespace App\Exports\Api;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithColumnFormatting,
    ShouldAutoSize,
    WithStyles,
    WithMapping
};
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use App\Constants\GeneralConst;

class PostListExport implements FromCollection, WithHeadings, WithColumnFormatting, ShouldAutoSize, WithStyles, WithMapping
{
    protected $posts;

    public function __construct(Collection $posts)
    {
        $this->posts = $posts;
    }

    /**
     * Use WithMapping to return raw data (for dates, return Carbon instance to keep Excel date formatting)
     *
     * @param  mixed $item
     * @return array
     */
    public function map($item): array
    {
        return [
            $item->id,
            $item->title,
            $item->description,
            GeneralConst::POST_STATUS[$item->status],
            $item->createdUser->name,
            $item->updatedUser->name,
            $item->deletedUser->name ?? '-',
            Carbon::parse($item->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($item->updated_at)->format('d-m-Y H:i:s'),
            $item->deleted_at ? Carbon::parse($item->deleted_at)->format('d-m-Y H:i:s') : '-',
        ];
    }

    /**
     * collection
     *
     * @return void
     */
    public function collection()
    {
        return $this->posts->values()->map(function ($item) {
            return $item;
        });
    }

    /**
     * headings
     *
     * @return array
     */
    public function headings(): array
    {
        return ['ID', 'Title', 'Description', 'Status', 'Created User', 'Updated User', 'Deleted User', 'Created At', 'Updated At', 'Deleted At'];
    }

    /**
     * Format columns & rows
     *
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_DATE_DATETIME,
            'I' => NumberFormat::FORMAT_DATE_DATETIME,
            'J' => NumberFormat::FORMAT_DATE_DATETIME,
        ];
    }

    /**
     * Style the header row bold with background color 
     *
     * @param  mixed $sheet
     * @return void
     */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        return [
            1 => [ // Header row
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'D9EDF7']],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ],
            "A2:J$lastRow" => [ // Data rows
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ],
        ];
    }
}
