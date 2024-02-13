<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\FileExt;
use App\Imports\ByStartRowImport;
use App\Models\Jabatan;
use App\Models\JabatanDasar;
use App\Models\GrupJabatan;
use App\Models\Pegawai;
use App\Models\Mutasi;
use App\Models\SK;

class JabatanController extends Controller
{    
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

        $jenis = in_array($request->query('jenis'), [1,2]) ? $request->query('jenis') : 1;
        $visibilitas = $request->query('visibilitas') == 1 ? 1 : 0;

        // Get SK
        $sk = SK::where('jenis_id','=',1)->where('status','=',1)->first();

        // Get grup jabatan
        $grup = GrupJabatan::whereHas('jabatan', function (Builder $query) use ($sk, $jenis) {
            return $query->where('sk_id','=',$sk->id)->where('jenis_id','=',$jenis);
        })->get();
		
		// View
		return view('admin/jabatan/index', [
			'grup' => $grup,
			'jenis' => $jenis,
			'visibilitas' => $visibilitas,
		]);
    }

    /**
     * Jabatan Remun.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function remun(Request $request)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

        // Get SK aktif
        $sk_aktif = SK::where('jenis_id','=',1)->where('status','=',1)->first();

        // Set SK
        $sk_id = $request->query('sk') ?: $sk_aktif->id;

        // Get SK
        $sk = SK::where('jenis_id','=',1)->orderBy('status','desc')->orderBy('tanggal','desc')->get();
		
		// Get jabatan
		$jabatan = Jabatan::where('sk_id','=',$sk_id)->get();
		
		// View
		return view('admin/jabatan/remun', [
			'sk' => $sk,
			'sk_aktif' => $sk_aktif,
			'sk_id' => $sk_id,
			'jabatan' => $jabatan
		]);
    }

    /**
     * Import from Excel
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        // Get SK
        $sk = SK::find(12);

		$array = Excel::toArray(new ByStartRowImport(2), public_path('storage/Jabatan_2024_01.xlsx'));

        if(count($array)>0) {
            foreach($array[0] as $data) {
                // Get jabatan aktif
                $jabatan_aktif = Jabatan::where('sk_id','=',1)->where('nama','=',$data[0])->where('sub','=',$data[1])->first();

                // Get grup jabatan
                if($data[1] == '-') {
                    $grup = GrupJabatan::where('nama','=',$data[0])->first();
                    if(!$grup) $grup = new GrupJabatan;
                    $grup->jenis_id = ($jabatan_aktif && $jabatan_aktif->jenis_id != 0) ? $jabatan_aktif->jenis_id : $data[4];
                    $grup->nama = $data[0];
                    $grup->bup = $data[3];
                    $grup->save();
                }
                else {
                    $grup = GrupJabatan::where('nama','=',$data[1])->first();
                    if(!$grup) $grup = new GrupJabatan;
                    $grup->jenis_id = ($jabatan_aktif && $jabatan_aktif->jenis_id != 0) ? $jabatan_aktif->jenis_id : $data[4];
                    $grup->nama = $data[1];
                    $grup->bup = $data[3];
                    $grup->save();
                }

                // Cek jabatan
                $jabatan = Jabatan::where('sk_id','=',$sk->id)->where('nama','=',$data[0])->where('sub','=',$data[1])->first();
                if(!$jabatan) $jabatan = new Jabatan;

                // Get jabatan dasar
                $jabatan_dasar = JabatanDasar::where('sk_id','=',$sk->id)->where('nama','=',$data[2])->first();

                // Simpan data jabatan
                $jabatan->grup_id = $grup->id;
                $jabatan->sk_id = $sk->id;
                $jabatan->jenis_id = $jabatan_aktif ? $jabatan_aktif->jenis_id : $grup->jenis_id;
                $jabatan->jabatan_dasar_id = $jabatan_dasar->id;
                $jabatan->nama = $data[0];
                $jabatan->sub = $data[1];
                $jabatan->save();
            }
        }
    }
}
