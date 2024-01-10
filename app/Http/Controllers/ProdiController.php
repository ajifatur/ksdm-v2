<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Imports\ProdiImport;
use App\Models\Prodi;

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
		$array = Excel::toArray(new ProdiImport, public_path('storage/Prodi_2024.xlsx'));

        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[0] != null) {
                    $prodi = Prodi::where('nama','=',$data[0])->first();
                    if(!$prodi) $prodi = new Prodi;
                    $prodi->nama = $data[0];
                    $prodi->save();
                }
            }
        }
    }
}
