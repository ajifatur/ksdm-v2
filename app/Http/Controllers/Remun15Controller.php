<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
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

class Remun15Controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Latest remun 15
        $latest = RemunInsentif::latest('tahun')->where('triwulan','=',15)->first();

        // Set tahun dan tanggal
        if($latest) {
            $tahun = $request->query('tahun') ?: $latest->tahun;
            $tanggal = $tahun.'-'.($latest->bulan < 10 ? '0'.$latest->bulan : $latest->bulan).'-01';
        }
        else {
            $tahun = $request->query('tahun') ?: date('Y');
            $tanggal = $tahun.'-'.(date('m')).'-01';
        }

        // Get remun 15
        $remun_15 = [];
        if($request->query('unit') != null && $request->query('unit') != 0)
            $remun_15 = RemunInsentif::where('triwulan','=',15)->where('tahun','=',$tahun)->where('unit_id','=',$request->query('unit'))->orderBy('remun_insentif','desc')->get();

        // Get unit
        $unit = Unit::where(function($query) use ($tanggal) {
			$query->where('start_date','<=',$tanggal)->orWhereNull('start_date');
		})->where(function($query) use ($tanggal) {
			$query->where('end_date','>=',$tanggal)->orWhereNull('end_date');
		})->where('nama','!=','-')->orderBy('num_order','asc')->get();

        // View
        return view('admin/remun-15/index', [
            'remun_15' => $remun_15,
            'tahun' => $tahun,
            'unit' => $unit,
        ]);
    }

    /**
     * Monitoring.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function monitoring(Request $request)
    {
        // Latest remun 15
        $latest = RemunInsentif::latest('tahun')->where('triwulan','=',15)->first();

        // Set tahun dan tanggal
        if($latest) {
            $tahun = $request->query('tahun') ?: $latest->tahun;
            $tanggal = $tahun.'-'.($latest->bulan < 10 ? '0'.$latest->bulan : $latest->bulan).'-01';
        }
        else {
            $tahun = $request->query('tahun') ?: date('Y');
            $tanggal = $tahun.'-'.(date('m')).'-01';
        }

        // Get unit
        $unit = Unit::where(function($query) use ($tanggal) {
			$query->where('start_date','<=',$tanggal)->orWhereNull('start_date');
		})->where(function($query) use ($tanggal) {
			$query->where('end_date','>=',$tanggal)->orWhereNull('end_date');
		})->where('nama','!=','-')->where('pusat','=',0)->orderBy('num_order','asc')->get();

        // Count total pegawai dan remun insentif
        $total_dosen_dibayarkan = 0;
        $total_tendik_dibayarkan = 0;
        $total_pegawai_dinolkan = 0;
        $total_nominal_dosen = 0;
        $total_nominal_tendik = 0;

        foreach($unit as $key=>$u) {
			// Count dosen dibayarkan
			$unit[$key]->dosen_dibayarkan = RemunInsentif::where('unit_id','=',$u->id)->where('triwulan','=',15)->where('tahun','=',$tahun)->whereIn('kategori',[1,3])->where('remun_insentif','>',0)->count();
			
			// Count tendik dibayarkan
			$unit[$key]->tendik_dibayarkan = RemunInsentif::where('unit_id','=',$u->id)->where('triwulan','=',15)->where('tahun','=',$tahun)->whereIn('kategori',[2])->where('remun_insentif','>',0)->count();
			
			// Count pegawai dinolkan
			$unit[$key]->pegawai_dinolkan = RemunInsentif::where('unit_id','=',$u->id)->where('triwulan','=',15)->where('tahun','=',$tahun)->where('remun_insentif','=',0)->count();
            
			// Count nominal dosen
            $unit[$key]->nominal_dosen = RemunInsentif::where('unit_id','=',$u->id)->where('triwulan','=',15)->where('tahun','=',$tahun)->whereIn('kategori',[1,3])->sum('remun_insentif');
			
			// Count nominal tendik
            $unit[$key]->nominal_tendik = RemunInsentif::where('unit_id','=',$u->id)->where('triwulan','=',15)->where('tahun','=',$tahun)->whereIn('kategori',[2])->sum('remun_insentif');

            // Sum
            $total_dosen_dibayarkan += $unit[$key]->dosen_dibayarkan;
            $total_tendik_dibayarkan += $unit[$key]->tendik_dibayarkan;
            $total_pegawai_dinolkan += $unit[$key]->pegawai_dinolkan;
            $total_nominal_dosen += $unit[$key]->nominal_dosen;
            $total_nominal_tendik += $unit[$key]->nominal_tendik;
        }

        // Get dosen dibayarkan pada pusat
        $dosen_dibayarkan_pusat = RemunInsentif::whereHas('unit', function(Builder $query) {
            return $query->where('pusat','=',1);
        })->where('triwulan','=',15)->where('tahun','=',$tahun)->whereIn('kategori',[1,3])->where('remun_insentif','>',0)->count();

        // Get tendik dibayarkan pada pusat
        $tendik_dibayarkan_pusat = RemunInsentif::whereHas('unit', function(Builder $query) {
            return $query->where('pusat','=',1);
        })->where('triwulan','=',15)->where('tahun','=',$tahun)->whereIn('kategori',[2])->where('remun_insentif','>',0)->count();

        // Get pegawai dinolkan pada pusat
        $pegawai_dinolkan_pusat = RemunInsentif::whereHas('unit', function(Builder $query) {
            return $query->where('pusat','=',1);
        })->where('triwulan','=',15)->where('tahun','=',$tahun)->where('remun_insentif','=',0)->count();

        // Get nominal dosen pada pusat
        $nominal_dosen_pusat = RemunInsentif::whereHas('unit', function(Builder $query) {
            return $query->where('pusat','=',1);
        })->where('triwulan','=',15)->where('tahun','=',$tahun)->whereIn('kategori',[1,3])->sum('remun_insentif');

        // Get nominal tendik pada pusat
        $nominal_tendik_pusat = RemunInsentif::whereHas('unit', function(Builder $query) {
            return $query->where('pusat','=',1);
        })->where('triwulan','=',15)->where('tahun','=',$tahun)->whereIn('kategori',[2])->sum('remun_insentif');

		// Sum
		$total_dosen_dibayarkan += $dosen_dibayarkan_pusat;
		$total_tendik_dibayarkan += $tendik_dibayarkan_pusat;
		$total_pegawai_dinolkan += $pegawai_dinolkan_pusat;
		$total_nominal_dosen += $nominal_dosen_pusat;
		$total_nominal_tendik += $nominal_tendik_pusat;

        // View
        return view('admin/remun-15/monitoring', [
            'unit' => $unit,
            'tahun' => $tahun,
            'dosen_dibayarkan_pusat' => $dosen_dibayarkan_pusat,
            'tendik_dibayarkan_pusat' => $tendik_dibayarkan_pusat,
            'pegawai_dinolkan_pusat' => $pegawai_dinolkan_pusat,
            'nominal_dosen_pusat' => $nominal_dosen_pusat,
            'nominal_tendik_pusat' => $nominal_tendik_pusat,
            'total_dosen_dibayarkan' => $total_dosen_dibayarkan,
            'total_tendik_dibayarkan' => $total_tendik_dibayarkan,
            'total_pegawai_dinolkan' => $total_pegawai_dinolkan,
            'total_nominal_dosen' => $total_nominal_dosen,
            'total_nominal_tendik' => $total_nominal_tendik,
        ]);
    }

    /**
     * Import.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get SK
        // $sk = SK::where('jenis_id','=',1)->where('status','=',1)->first();
        $sk = SK::find(1);

        // Get array
		$array = Excel::toArray(new RemunInsentifImport, public_path('storage/Remun_15_2023.xlsx'));

        $error = [];
        $nama = [];
        $jabs = [];
        if(count($array)>0) {
            foreach($array[0] as $key=>$data) {
                // Get pegawai
                $pegawai = Pegawai::where('nip','=',$data[0])->first();

                if($pegawai) {
                    // Get status kepegawaian
                    $status_kepegawaian = StatusKepegawaian::where('nama','=',$data[4])->first();

                    // Get golongan
                    $golongan = Golongan::where('nama','=',$data[2])->first();

                    // Get jabatan
                    $jabatan = Jabatan::where('sk_id','=',$sk->id)->where('nama','=',$data[6])->where('sub','=',$data[7])->first();

                    if(!$jabatan) {
                        $jabatan = $pegawai->remun_gaji()->where('bulan','<=',11)->latest('tahun')->latest('bulan')->first()->jabatan;
                        array_push($error, $data[6]);
                        array_push($nama, $pegawai->nama);
                        array_push($jabs, $jabatan->nama);
                    }

                    // Get unit
                    $unit = Unit::where('nama','=',$data[1])->first();

                    // Get kategori
                    if($data[5] == 'DT') $kategori = 3;
                    elseif($data[5] == 'DOSEN' || $data[5] == 'Dosen') $kategori = 1;
                    elseif($data[5] == 'TENDIK' || $data[5] == 'Tendik') $kategori = 2;
                    else $kategori = 0;

                    // Get remun insentif
                    $remun_insentif = RemunInsentif::where('pegawai_id','=',$pegawai->id)->where('triwulan','=',15)->where('bulan','=',11)->where('tahun','=',2023)->first();
                    if(!$remun_insentif) $remun_insentif = new RemunInsentif;

                    // Simpan remun insentif
                    $remun_insentif->pegawai_id = $pegawai->id;
                    $remun_insentif->golongan_id = $golongan ? $golongan->id : 0;
                    $remun_insentif->status_kepeg_id = $status_kepegawaian ? $status_kepegawaian->id : 0;
                    $remun_insentif->jabatan_dasar_id = $jabatan ? $jabatan->jabatan_dasar_id : 0;
                    $remun_insentif->jabatan_id = $jabatan ? $jabatan->id : 0;
                    $remun_insentif->unit_id = $unit ? $unit->id : 0;
                    $remun_insentif->layer_id = $unit ? $unit->layer_id : 0;
                    $remun_insentif->triwulan = 15;
                    $remun_insentif->bulan = 11;
                    $remun_insentif->tahun = 2023;
                    $remun_insentif->kategori = $kategori;
                    $remun_insentif->poin = str_replace(',','.',$data[8]);
                    $remun_insentif->remun_insentif = $data[9];
                    $remun_insentif->keterangan = $data[10];
                    $remun_insentif->num_order = $key+1;
                    $remun_insentif->save();
                }
            }
        }
        var_dump($error, $nama, $jabs);
    }
}
