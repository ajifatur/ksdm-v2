<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\DateTimeExt;
use App\Imports\GolruImport;
use App\Models\Golru;

class GolruController extends Controller
{    
    /**
     * Import from Excel
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Set file
        $array = Excel::toArray(new GolruImport, public_path('assets/spreadsheets/Golru.xlsx'));

        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[0] != null) {
                    // Simpan golru
                    $golru = Golru::where('golongan_id','=',$data[0])->where('ruang','=',$data[1])->where('nama','=',$data[3])->first();
                    if(!$golru) $golru = new Golru;
                    $golru->golongan_id = $data[0];
                    $golru->ruang = $data[1];
                    $golru->nama = $data[3];
                    $golru->save();
                }
            }
        }
    }
}