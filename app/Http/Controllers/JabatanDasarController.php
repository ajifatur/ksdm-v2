<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\FileExt;
use App\Imports\JabatanDasarImport;
use App\Models\JabatanDasar;
use App\Models\SK;

class JabatanDasarController extends Controller
{    
    /**
     * Import from Excel
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        // Get SK
        $sk = SK::find(7);

		$array = Excel::toArray(new JabatanDasarImport, public_path('assets/spreadsheets/Jabatan_Dasar_2_April.xlsx'));

        if(count($array)>0) {
            foreach($array[0] as $data) {
                // Cek jabatan dasar
                $jabatan_dasar = JabatanDasar::where('sk_id','=',$sk->id)->where('nama','=',$data[0])->first();
                if(!$jabatan_dasar) $jabatan_dasar = new JabatanDasar;

                // Simpan data jabatan dasar
                $jabatan_dasar->sk_id = $sk->id;
                $jabatan_dasar->nama = $data[0];
                $jabatan_dasar->grade = $data[1];
                $jabatan_dasar->nilai = $data[2];
                $jabatan_dasar->koefisien = $data[3];
                $jabatan_dasar->pir = $sk->pir;
                $jabatan_dasar->save();
            }
        }
    }
}
