<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Pegawai;

class KGBController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
		$tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';
		$tmt = [];
		
		// Get TMT golongan III dan IV
		for($i = $tahun; $i >= ($tahun - 32); $i-=2) {
			array_push($tmt, $i.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01');
		}
		
		// Get pegawai berdasarkan TMT golongan
		$pegawai = Pegawai::whereHas('golru', function(Builder $query) {
			return $query->whereIn('golongan_id',[3,4]);
		})->where('status_kerja_id','=',1)->whereIn('status_kepeg_id',[1,2])->whereIn('tmt_golongan',$tmt)->orderBy('tmt_golongan','asc')->get();
		foreach($pegawai as $key=>$p) {
			// Get mutasi KP / KGB sebelumnya
			$pegawai[$key]->mutasi_sebelum = $p->mutasi()->whereHas('jenis', function(Builder $query) {
				return $query->whereIn('nama',['Mutasi Pangkat','KGB','PMK']);
			})->where('tmt','<',$tanggal)->first();
		}

        // View
        return view('admin/kgb/index', [
            'pegawai' => $pegawai,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
    }
}
