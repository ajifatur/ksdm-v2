<?php

namespace App\Exports;

use App\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class TestExport extends StringValueBinder implements WithCustomValueBinder, FromView, WithEvents
{
	use Exportable;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function bindValue(Cell $cell, $value)
    {
        // if(is_numeric($value)) {
        //     if($cell->getColumn() != 'C') {
        //         $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);
        //         return true;
        //     }
        // }
        if(is_numeric($value)) {
            if($cell->getColumn() != 'A') {
                $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);
                return true;
            }
        }

        // else return default behavior
        return parent::bindValue($cell, $value);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
    	// View
    	return view('admin/test/export-2', [
    		'data' => $this->data
    	]);
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            // AfterSheet::class => function(AfterSheet $event) {
            //     $event->sheet->getDelegate()->getStyle('A1:P3')->getFont()->setSize(12);
            //     $event->sheet->getDelegate()->getStyle('A6:P6')->getFont()->setSize(8);
            //     $event->sheet->getDelegate()->getStyle('A5:P'.(count($this->data) + 6))->getAlignment()->setWrapText(true);
            //     $event->sheet->getDelegate()->getStyle('A5:P'.(count($this->data) + 6))->applyFromArray([
            //         'borders' => [
            //             'allBorders' => [
            //                 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            //                 'color' => ['argb' => '000000'],
            //             ],
            //         ]
            //     ]);
            //     $event->sheet->setAutoFilter('A5:P'.(count($this->data) + 6));
            // },
        ];
    }
}