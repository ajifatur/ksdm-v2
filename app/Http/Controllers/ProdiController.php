<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Imports\ByStartRowImport;
use App\Models\Prodi;
use App\Models\KriteriaProdi;

class ProdiController extends Controller
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
     * Import from Excel
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
		$array = Excel::toArray(new ByStartRowImport(2), public_path('storage/Prodi_2024.xlsx'));

        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[0] != null) {
                    // Get prodi
                    $prodi = Prodi::where('nama','=',$data[0])->first();

                    // Simpan kriteria prodi
                    $kriteria_prodi = KriteriaProdi::where('prodi_id','=',$prodi->id)->where('tahun','=',2024)->first();
                    if(!$kriteria_prodi) $kriteria_prodi = new KriteriaProdi;
                    $kriteria_prodi->prodi_id = $prodi->id;
                    $kriteria_prodi->tahun = 2024;
                    $kriteria_prodi->jumlah = $data[3];
                    $kriteria_prodi->kriteria = substr($data[1],strlen($data[1])-1);
                    $kriteria_prodi->save();
                }
            }
        }
    }
}
