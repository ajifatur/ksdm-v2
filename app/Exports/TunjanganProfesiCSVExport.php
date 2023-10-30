<?php

namespace App\Exports;

use App\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class TunjanganProfesiCSVExport extends StringValueBinder implements WithCustomValueBinder, WithCustomCsvSettings, FromView
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
        if(is_numeric($value)) {
            if($cell->getColumn() == 'E') {
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
    	return view('admin/tunjangan-profesi/csv', [
    		'data' => $this->data
    	]);
    }
    
    /**
    * @return array
    */
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => "|",
            'enclosure' => ''
        ];
    }
}