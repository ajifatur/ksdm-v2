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
use App\Imports\GajiPokokImport;
use App\Models\GajiPokok;
use App\Models\Golru;
use App\Models\Mutasi;

class GajiPokokController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()) {
            if($request->query('golru') != null) {
                $golru = $request->query('golru');
                $gaji_pokok = GajiPokok::where('golru_id','=',$golru)->get();
                return response()->json($gaji_pokok);
            }
            elseif($request->query('pegawai') != null) {
                $mutasi = Mutasi::where('pegawai_id','=',$request->query('pegawai'))->orderBy('tahun','desc')->orderBy('bulan','desc')->first();
                $gaji_pokok = GajiPokok::where('golru_id','=',$mutasi->golru_id)->get();
                return response()->json([
                    'id' => $mutasi->gaji_pokok_id,
                    'gaji_pokok' => $gaji_pokok
                ]);
            }
        }
    }

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
        $array = Excel::toArray(new GajiPokokImport, public_path('assets/spreadsheets/Gaji Pokok.xlsx'));

        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[0] != null) {
                    // Get golru
                    $golru = Golru::where('golongan_id','=',$data[2])->where('ruang','=',$data[3])->first();
                    
                    // Simpan gaji pokok
                    $gaji_pokok = GajiPokok::where('golru_id','=',$golru->id)->where('nama','=',$data[4])->where('mkg','=',$data[5])->first();
                    if(!$gaji_pokok) $gaji_pokok = new GajiPokok;
                    $gaji_pokok->golru_id = $golru->id;
                    $gaji_pokok->nama = $data[4];
                    $gaji_pokok->mkg = $data[5];
                    $gaji_pokok->gaji_pokok = $data[1];
                    $gaji_pokok->save();
                }
            }
        }
    }
}