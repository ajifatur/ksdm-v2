<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithStartRow;

class RemunInsentifImport implements ToArray, WithStartRow
{		
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
        return 2;
    }
}