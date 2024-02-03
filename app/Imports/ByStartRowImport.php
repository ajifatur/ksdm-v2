<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ByStartRowImport implements ToArray, WithStartRow
{
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($row)
    {
        $this->row = $row;
    }
    
    /**
    * @param array $array
    */
    public function array(array $array)
    {
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return $this->row;
    }
}