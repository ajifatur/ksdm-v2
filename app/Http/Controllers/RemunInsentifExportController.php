<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\DateTimeExt;
use App\Exports\RemunInsentifExport;
use App\Exports\RemunInsentifRecapExport;
use App\Exports\RemunInsentifPusatExport;
use App\Exports\RemunInsentifZakatExport;
use App\Models\RemunInsentif;
use App\Models\LebihKurang;
use App\Models\Pegawai;
use App\Models\SK;
use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Unit;
use App\Models\StatusKepegawaian;

class RemunInsentifExportController extends Controller
{
    /**
     * Single.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function single(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        $kategori = $request->query('kategori');
        $unit = $request->query('unit');
        $status = $request->query('status');
        $triwulan = $request->query('triwulan');
        $tahun = $request->query('tahun');

        // Get unit
        $get_unit = Unit::findOrFail($unit);

        // Get kategori
        $get_kategori = $kategori == 1 ? 'Dosen' : 'Tendik';

        // Get remun insentif
        if($status == 1) {
            if($kategori == 1)
                $remun_insentif = RemunInsentif::whereHas('pegawai', function(Builder $query) {
                    return $query->whereNotIn('status_kerja_id',[2,3]);
                })->where('unit_id','=',$unit)->whereIn('kategori',[1,3])->where('remun_insentif','>',0)->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->orderBy('num_order','asc')->get();
            elseif($kategori == 2)
                $remun_insentif = RemunInsentif::whereHas('pegawai', function(Builder $query) {
                    return $query->whereNotIn('status_kerja_id',[2,3]);
                })->where('unit_id','=',$unit)->where('kategori','=',2)->where('remun_insentif','>',0)->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->orderBy('num_order','asc')->get();
        }
        else {
            if($kategori == 1)
                $remun_insentif = RemunInsentif::whereHas('pegawai', function(Builder $query) {
                    return $query->whereIn('status_kerja_id',[2,3]);
                })->where('unit_id','=',$unit)->whereIn('kategori',[1,3])->where('remun_insentif','>',0)->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->orderBy('num_order','asc')->get();
            elseif($kategori == 2)
                $remun_insentif = RemunInsentif::whereHas('pegawai', function(Builder $query) {
                    return $query->whereIn('status_kerja_id',[2,3]);
                })->where('unit_id','=',$unit)->where('kategori','=',2)->where('remun_insentif','>',0)->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->orderBy('num_order','asc')->get();
        }

        // Return
        return Excel::download(new RemunInsentifExport($remun_insentif), 'Remun Insentif Triwulan '.$request->query('triwulan').' '.$get_unit->nama.' '.$get_kategori.' ('.($status == 1 ? 'Aktif' : 'Pensiun-MD').').xlsx');
    }

    /**
     * Pusat.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function pusat(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");
        
        $status = $request->query('status');
        $triwulan = $request->query('triwulan');
        $tahun = $request->query('tahun');

        // Get unit
        $unit = Unit::where('pusat','=',1)->orderBy('num_order_remun','asc')->get();
        
        // Get remun insentif
        $remun_insentif = [];
        if($status == 1) {
            foreach($unit as $u) {
                $ri = RemunInsentif::whereHas('pegawai', function(Builder $query) {
                    return $query->whereNotIn('status_kerja_id',[2,3]);
                })->where('unit_id','=',$u->id)->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->where('remun_insentif','>',0)->orderBy('num_order','asc')->get();
                array_push($remun_insentif, $ri);
            }
        }
        else {
            foreach($unit as $u) {
                $ri = RemunInsentif::whereHas('pegawai', function(Builder $query) {
                    return $query->whereIn('status_kerja_id',[2,3]);
                })->where('unit_id','=',$u->id)->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->where('remun_insentif','>',0)->orderBy('num_order','asc')->get();
                array_push($remun_insentif, $ri);
            }
        }

        // Return
        return Excel::download(new RemunInsentifPusatExport($remun_insentif), 'Remun Insentif Triwulan '.$request->query('triwulan').' Pusat ('.($status == 1 ? 'Aktif' : 'Pensiun-MD').').xlsx');
    }

    /**
     * Recap.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function recap(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        $tahun = $request->query('tahun');
        $triwulan = $request->query('triwulan');

        // Remun Insentif
        $remun_insentif = RemunInsentif::where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->orderBy('remun_insentif','desc')->get();

        // Return
        return Excel::download(new RemunInsentifRecapExport($remun_insentif), 'Rekap Remun Insentif ('.$tahun.' Triwulan '.$triwulan.').xlsx');
    }

    /**
     * Zakat.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function zakat(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        $pensiun = $request->query('pensiun');
        $unit = $request->query('unit');
        $pusat = $request->query('pusat');
        $triwulan = $request->query('triwulan');
        $tahun = $request->query('tahun');

        // Set tanggal
        $bulan = $triwulan * 3;
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';

        // Set romawi
        $romawi = ['I','II','III','IV'];

        if($pensiun != 1) {
            if($pusat != 1) {
                // Get unit
                $unit = Unit::findOrFail($request->query('unit'));

                // Get pegawai dalam unit berdasarkan remun insentif
                $remun_insentif_dosen = RemunInsentif::whereHas('pegawai', function(Builder $query) use ($tanggal) {
                    return $query->whereNotIn('status_kerja_id',[2,3])->orWhere('tmt_non_aktif','>',$tanggal);
                })->where('unit_id','=',$request->query('unit'))->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[1,3])->where('remun_insentif','>',0)->orderBy('num_order','asc')->get();
                $remun_insentif_tendik = RemunInsentif::whereHas('pegawai', function(Builder $query) use ($tanggal) {
                    return $query->whereNotIn('status_kerja_id',[2,3])->orWhere('tmt_non_aktif','>',$tanggal);
                })->where('unit_id','=',$request->query('unit'))->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[2])->where('remun_insentif','>',0)->orderBy('num_order','asc')->get();
            }
            else {
                // Get unit pusat
                $unit = Unit::where('pusat','=',1)->pluck('id')->toArray();

                // Get pegawai dalam unit berdasarkan remun insentif
                $remun_insentif_dosen = RemunInsentif::whereHas('pegawai', function(Builder $query) use ($tanggal) {
                    return $query->whereNotIn('status_kerja_id',[2,3])->orWhere('tmt_non_aktif','>',$tanggal);
                })->whereIn('unit_id',$unit)->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[1,3])->where('remun_insentif','>',0)->orderBy('num_order','asc')->get();
                $remun_insentif_tendik = RemunInsentif::whereHas('pegawai', function(Builder $query) use ($tanggal) {
                    return $query->whereNotIn('status_kerja_id',[2,3])->orWhere('tmt_non_aktif','>',$tanggal);
                })->whereIn('unit_id',$unit)->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[2])->where('remun_insentif','>',0)->orderBy('num_order','asc')->get();
            }
        }
        else {
            // Get unit
            $unit = null;

            // Get pegawai dalam unit berdasarkan remun insentif
            $remun_insentif_dosen = RemunInsentif::whereHas('pegawai', function(Builder $query) use ($tanggal) {
                return $query->whereIn('status_kerja_id',[2])->where('tmt_non_aktif','<=',$tanggal);
            })->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[1,3])->where('remun_insentif','>',0)->orderBy('num_order','asc')->get();
            $remun_insentif_tendik = RemunInsentif::whereHas('pegawai', function(Builder $query) use ($tanggal) {
                return $query->whereIn('status_kerja_id',[2])->where('tmt_non_aktif','<=',$tanggal);
            })->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[2])->where('remun_insentif','>',0)->orderBy('num_order','asc')->get();
        }

        // Set title
        if($pensiun != 1)
            $title = 'Potongan Zakat '.($pusat != 1 ? $unit->nama : 'Pusat').' ('.$tahun.' Triwulan '.$triwulan.')';
        else
            $title = 'Potongan Zakat Pegawai Pensiun ('.$tahun.' Triwulan '.$triwulan.')';

        // Return
        return Excel::download(new RemunInsentifZakatExport([
            'title' => $title,
            'pensiun' => $pensiun,
            'unit' => $unit,
            'triwulan' => $triwulan,
            'tahun' => $tahun,
            'romawi' => $romawi[$triwulan-1],
            'remun_insentif_dosen' => $remun_insentif_dosen,
            'remun_insentif_tendik' => $remun_insentif_tendik,
        ]), $title.'.xlsx');
    }
}