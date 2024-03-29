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
use App\Exports\GajiPokokExport;
use App\Imports\ByStartRowImport;
use App\Models\GajiPokok;
use App\Models\Golru;
use App\Models\Mutasi;
use App\Models\SK;

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
                $gaji_pokok = GajiPokok::where('sk_id','=',$request->query('sk'))->where('golru_id','=',$golru)->get();
                return response()->json($gaji_pokok);
            }
            elseif($request->query('pegawai') != null) {
                $mutasi = Mutasi::where('pegawai_id','=',$request->query('pegawai'))->orderBy('tahun','desc')->orderBy('bulan','desc')->first();
                $gaji_pokok = GajiPokok::where('sk_id','=',$request->query('sk'))->where('golru_id','=',$mutasi->golru_id)->get();
                return response()->json([
                    'id' => $mutasi->gaji_pokok_id,
                    'gaji_pokok' => $gaji_pokok
                ]);
            }
        }

        // Get SK aktif
        $sk_aktif = SK::where('jenis_id','=',5)->where('status','=',1)->first();

        // Set SK
        $sk_id = $request->query('sk') ?: $sk_aktif->id;

        // Get SK
        $sk = SK::where('jenis_id','=',5)->orderBy('status','desc')->orderBy('tanggal','desc')->get();
        
        // Get golru
        $golru = Golru::all();
        
        // View
        return view('admin/gaji-pokok/index', [
            'golru' => $golru,
            'sk_aktif' => $sk_aktif,
            'sk_id' => $sk_id,
            'sk' => $sk,
        ]);
    }

    /**
     * Export to Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
		ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "-1");
        
        // Get SK
        $sk = SK::find($request->query('sk'));

        // Get gaji pokok
        $gaji_pokok = GajiPokok::where('sk_id','=',$sk->id)->orderBy('nama','asc')->get();

        // Return
        return Excel::download(new GajiPokokExport([
            'gaji_pokok' => $gaji_pokok,
        ]), 'Gaji Pokok Berdasarkan '.$sk->nama.'.xlsx');
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
        $array = Excel::toArray(new ByStartRowImport(2), public_path('storage/Gaji Pokok PNS 2024.xlsx'));

        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[0] != null) {
                    // Get golru
                    $golru = Golru::where('golongan_id','=',substr($data[0],0,1))->where('ruang','=',substr($data[0],1,1))->first();
                    
                    // Simpan gaji pokok
                    $gaji_pokok = GajiPokok::where('golru_id','=',$golru->id)->where('sk_id','=',14)->where('nama','=',$data[0])->where('mkg','=',substr($data[0],2,2))->first();
                    if(!$gaji_pokok) $gaji_pokok = new GajiPokok;
                    $gaji_pokok->golru_id = $golru->id;
                    $gaji_pokok->sk_id = 14;
                    $gaji_pokok->nama = $data[0];
                    $gaji_pokok->mkg = substr($data[0],2,2);
                    $gaji_pokok->gaji_pokok = $data[1];
                    $gaji_pokok->save();
                }
            }
        }
    }
}