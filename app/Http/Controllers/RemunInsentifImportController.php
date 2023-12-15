<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use PDF;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\DateTimeExt;
use App\Imports\RemunInsentifImport;
use App\Models\RemunInsentif;
use App\Models\LebihKurang;
use App\Models\Pegawai;
use App\Models\SK;
use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Unit;
use App\Models\StatusKepegawaian;

class RemunInsentifImportController extends Controller
{
    /**
     * Zakat
     *
     * @return \Illuminate\Http\Response
     */
    public function zakat(Request $request)
    {
        if($request->method() == 'GET') {
            // Get unit
            $unit = Unit::all();

            // View
            return view('admin/remun-insentif/import/zakat', [
                'unit' => $unit,
            ]);
        }
        elseif($request->method() == 'POST') {
            ini_set("memory_limit", "-1");
            ini_set("max_execution_time", "-1");
            
            // Make directory if not exists
            if(!File::exists(public_path('storage/spreadsheets/zakat')))
                File::makeDirectory(public_path('storage/spreadsheets/zakat'));
        }
    }
}
