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

class RemunInsentifController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Latest remun insentif
        $latest = RemunInsentif::latest('tahun')->latest('triwulan')->first();

        // Get triwulan dan tahun
        $triwulan = $request->query('triwulan') ?: $latest->triwulan;
        $tahun = $request->query('tahun') ?: $latest->tahun;

        // Set tanggal
        $bulan = $triwulan * 3;
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';

        // Get remun insentif
        $remun_insentif = [];
        if($request->query('unit') != null && $request->query('unit') != 0)
            $remun_insentif = RemunInsentif::where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->where('unit_id','=',$request->query('unit'))->orderBy('remun_insentif','desc')->get();

        // Get unit
        $unit = Unit::where('end_date','>=',$tanggal)->orWhere('end_date','=',null)->where('nama','!=','-')->orderBy('num_order','asc')->get();

        // View
        return view('admin/remun-insentif/index', [
            'remun_insentif' => $remun_insentif,
            'triwulan' => $triwulan,
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
        // Latest remun insentif
        $latest = RemunInsentif::latest('tahun')->latest('triwulan')->first();

        // Get triwulan dan tahun
        $triwulan = $request->query('triwulan') ?: $latest->triwulan;
        $tahun = $request->query('tahun') ?: $latest->tahun;

        // Set tanggal
        $bulan = $triwulan * 3;
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';

        // Get unit
        $unit = Unit::where('end_date','>=',$tanggal)->orWhere('end_date','=',null)->where('nama','!=','-')->where('pusat','=',0)->orderBy('num_order','asc')->get();

        // Count total pegawai dan remun insentif
        $total_pegawai = 0;
        $total_remun_insentif = 0;
        $total_potongan = 0;
        $total_dibayarkan = 0;

        foreach($unit as $key=>$u) {
            // Get pensiun dan MD
            $pensiunmd = RemunInsentif::where('unit_id','=',$u->id)->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereHas('pegawai', function(Builder $query) use ($tanggal) {
                return $query->whereIn('status_kerja_id',[2,3])->where('tmt_non_aktif','<=',$tanggal);
            })->get();

            // Get pegawai pensiun dan MD
            $arrayp = [];
            if(count($pensiunmd) > 0) {
                foreach($pensiunmd as $p) {
                    array_push($arrayp, strtoupper(title_name($p->pegawai->nama, $p->pegawai->gelar_depan, $p->pegawai->gelar_belakang)));
                }
            }
            
            // Push
            $unit[$key]->pensiunmd = $pensiunmd;
            $unit[$key]->namapensiunmd = $arrayp;
            $unit[$key]->pegawai = RemunInsentif::where('unit_id','=',$u->id)->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->count();
            $unit[$key]->remun_insentif = RemunInsentif::where('unit_id','=',$u->id)->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->sum('remun_insentif');
            $unit[$key]->potongan = LebihKurang::whereIn('pegawai_id',RemunInsentif::where('unit_id','=',$u->id)->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray())->where('triwulan_proses','=',$triwulan)->where('tahun_proses','=',$tahun)->sum('selisih');
            $unit[$key]->dibayarkan = $unit[$key]->remun_insentif + $unit[$key]->potongan;
            $unit[$key]->potonganDosen = LebihKurang::whereIn('pegawai_id',RemunInsentif::where('unit_id','=',$u->id)->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[1,3])->pluck('pegawai_id')->toArray())->where('triwulan_proses','=',$triwulan)->where('tahun_proses','=',$tahun)->count();
            $unit[$key]->potonganTendik = LebihKurang::whereIn('pegawai_id',RemunInsentif::where('unit_id','=',$u->id)->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[2])->pluck('pegawai_id')->toArray())->where('triwulan_proses','=',$triwulan)->where('tahun_proses','=',$tahun)->count();

            // Sum
            $total_pegawai += $unit[$key]->pegawai;
            $total_remun_insentif += $unit[$key]->remun_insentif;
            $total_potongan += $unit[$key]->potongan;
            $total_dibayarkan += $unit[$key]->dibayarkan;
        }

        // Get pensiun dan MD pusat
        $pensiunmd_pusat = RemunInsentif::where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereHas('unit', function(Builder $query) {
            return $query->where('pusat','=',1);
        })->whereHas('pegawai', function(Builder $query) use ($tanggal) {
            return $query->whereIn('status_kerja_id',[2,3])->where('tmt_non_aktif','<=',$tanggal);
        })->get();
        
        // Get pegawai pensiun dan MD pusat
        $pegawai_pensiunmd_pusat = [];
        if(count($pensiunmd_pusat) > 0) {
            foreach($pensiunmd_pusat as $p) {
                array_push($pegawai_pensiunmd_pusat, strtoupper(title_name($p->pegawai->nama, $p->pegawai->gelar_depan, $p->pegawai->delar_belakang)));
            }
        }

        // Get pegawai pusat
        $pegawai_pusat = RemunInsentif::whereHas('unit', function(Builder $query) {
            return $query->where('pusat','=',1);
        })->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->count();

        // Get remun insentif pusat
        $remun_insentif_pusat = RemunInsentif::whereHas('unit', function(Builder $query) {
            return $query->where('pusat','=',1);
        })->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->sum('remun_insentif');

        // Get potongan pusat
        $potongan_pusat = LebihKurang::whereIn('pegawai_id',RemunInsentif::whereHas('unit',
            function(Builder $query) {
                return $query->where('pusat','=',1);
            })->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray()
        )->where('triwulan_proses','=',$triwulan)->where('tahun_proses','=',$tahun)->sum('selisih');

        // Get potongan pegawai pusat
        $potongan_pegawai_pusat['dosen'] = LebihKurang::whereIn('pegawai_id',RemunInsentif::whereHas('unit',
            function(Builder $query) {
                return $query->where('pusat','=',1);
            })->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[1,3])->pluck('pegawai_id')->toArray()
        )->where('triwulan_proses','=',$triwulan)->where('tahun_proses','=',$tahun)->count();
        $potongan_pegawai_pusat['tendik'] = LebihKurang::whereIn('pegawai_id',RemunInsentif::whereHas('unit',
            function(Builder $query) {
                return $query->where('pusat','=',1);
            })->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[2])->pluck('pegawai_id')->toArray()
        )->where('triwulan_proses','=',$triwulan)->where('tahun_proses','=',$tahun)->count();

        // Sum
        $total_pegawai += $pegawai_pusat;
        $total_remun_insentif += $remun_insentif_pusat;
        $total_potongan += $potongan_pusat;
        $total_dibayarkan += ($remun_insentif_pusat + $potongan_pusat);

        // View
        return view('admin/remun-insentif/monitoring', [
            'unit' => $unit,
            'triwulan' => $triwulan,
            'tahun' => $tahun,
            'pensiunmd_pusat' => $pensiunmd_pusat,
            'pegawai_pensiunmd_pusat' => $pegawai_pensiunmd_pusat,
            'pegawai_pusat' => $pegawai_pusat,
            'remun_insentif_pusat' => $remun_insentif_pusat,
            'potongan_pusat' => $potongan_pusat,
            'potongan_pegawai_pusat' => $potongan_pegawai_pusat,
            'total_pegawai' => $total_pegawai,
            'total_remun_insentif' => $total_remun_insentif,
            'total_potongan' => $total_potongan,
            'total_dibayarkan' => $total_dibayarkan,
        ]);
    }

    /**
     * Print Potongan PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function printPotongan(Request $request)
    {
        // Check the access
        // has_access(method(__METHOD__), Auth::user()->role_id);

		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        $kategori = $request->query('kategori');
        $unit = $request->query('unit');
        $pusat = $request->query('pusat');
        $triwulan = $request->query('triwulan');
        $tahun = $request->query('tahun');

        // Get kategori
        $get_kategori = $kategori == 1 ? 'Dosen' : 'Tendik';

        if($pusat != 1) {
            // Get unit
            $unit = Unit::findOrFail($request->query('unit'));

            // Get pegawai dalam unit berdasarkan remun insentif
            if($kategori == 1)
                $remun_insentif = RemunInsentif::where('unit_id','=',$request->query('unit'))->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[1,3])->orderBy('num_order','desc')->get();
            elseif($kategori == 2)
                $remun_insentif = RemunInsentif::where('unit_id','=',$request->query('unit'))->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[2])->orderBy('num_order','desc')->get();
        }
        else {
            // Get unit pusat
            $unit = Unit::where('pusat','=',1)->pluck('id')->toArray();

            // Get pegawai dalam unit berdasarkan remun insentif
            if($kategori == 1)
                $remun_insentif = RemunInsentif::whereIn('unit_id',$unit)->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[1,3])->orderBy('num_order','desc')->get();
            elseif($kategori == 2)
                $remun_insentif = RemunInsentif::whereIn('unit_id',$unit)->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[2])->orderBy('num_order','desc')->get();
        }

        // Get potongan
        $potongan = LebihKurang::whereIn('pegawai_id',$remun_insentif->pluck('pegawai_id')->toArray())->where('triwulan_proses','=',$triwulan)->where('tahun_proses','=',$tahun)->get();

        foreach($potongan as $key=>$p) {
            // Get remun insentif
            $potongan[$key]->remun_insentif = RemunInsentif::where('pegawai_id','=',$p->pegawai_id)->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->first();
        }

        // Set title
        $title = 'Potongan Remun Insentif '.($pusat != 1 ? $unit->nama : 'Pusat').' '.$get_kategori.' ('.$tahun.' Triwulan '.$triwulan.')';

        // PDF
        $pdf = \PDF::loadView('admin/remun-insentif/print-potongan', [
            'title' => $title,
            'unit' => $unit,
            'kategori' => $kategori,
            'triwulan' => $triwulan,
            'tahun' => $tahun,
            'remun_insentif' => $remun_insentif,
            'potongan' => $potongan,
        ]);
        $pdf->setPaper([0, 0 , 935, 612]);
        return $pdf->stream($title.'.pdf');
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
        $sk = SK::find(2);

        // Get array
		$array = Excel::toArray(new RemunInsentifImport, public_path('assets/spreadsheets/Remun_Triwulan_II_2023.xlsx'));

        $error = [];
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
                        $jabatan = $pegawai->remun_gaji()->where('bulan','<=',6)->latest('tahun')->latest('bulan')->first()->jabatan;
                        array_push($error, $data[11]);
                        array_push($jabs, $jabatan->nama);
                    }

                    // Get unit
                    $unit = Unit::where('nama','=',$data[1])->first();

                    // Get kategori
                    if($data[5] == 'DT') $kategori = 3;
                    elseif($data[5] == 'DOSEN') $kategori = 1;
                    elseif($data[5] == 'TENDIK') $kategori = 2;
                    else $kategori = 0;

                    // Get remun insentif
                    $remun_insentif = RemunInsentif::where('pegawai_id','=',$pegawai->id)->where('triwulan','=',2)->where('bulan','=',6)->where('tahun','=',2023)->first();
                    if(!$remun_insentif) $remun_insentif = new RemunInsentif;

                    // Simpan remun insentif
                    $remun_insentif->pegawai_id = $pegawai->id;
                    $remun_insentif->golongan_id = $golongan ? $golongan->id : 0;
                    $remun_insentif->status_kepeg_id = $status_kepegawaian ? $status_kepegawaian->id : 0;
                    $remun_insentif->jabatan_dasar_id = $jabatan ? $jabatan->jabatan_dasar_id : 0;
                    $remun_insentif->jabatan_id = $jabatan ? $jabatan->id : 0;
                    $remun_insentif->unit_id = $unit ? $unit->id : 0;
                    $remun_insentif->layer_id = $unit ? $unit->layer_id : 0;
                    $remun_insentif->triwulan = 2;
                    $remun_insentif->bulan = 6;
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
        var_dump($error, $jabs);
    }
}
