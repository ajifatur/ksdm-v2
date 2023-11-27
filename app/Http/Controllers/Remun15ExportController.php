<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\DateTimeExt;
use App\Exports\Remun15Export;
use App\Exports\RemunInsentifPusatExport;
use App\Exports\RemunInsentifRecapExport;
use App\Models\RemunInsentif;
use App\Models\LebihKurang;
use App\Models\Pegawai;
use App\Models\SK;
use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Unit;
use App\Models\StatusKepegawaian;

class Remun15ExportController extends Controller
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
        $triwulan = 15;
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
        return Excel::download(new Remun15Export($remun_insentif), 'Remun-15 '.$get_unit->nama.' '.$get_kategori.' ('.($status == 1 ? 'Aktif' : 'Pensiun-MD').').xlsx');
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

        // Get unit
        $unit = Unit::where('pusat','=',1)->orderBy('num_order_remun','asc')->get();
        
        // Get remun insentif
        $remun_insentif = [];
        if($status == 1) {
            foreach($unit as $u) {
                $ri = RemunInsentif::whereHas('pegawai', function(Builder $query) {
                    return $query->whereNotIn('status_kerja_id',[2,3]);
                })->where('unit_id','=',$u->id)->where('remun_insentif','>',0)->orderBy('num_order','asc')->get();
                array_push($remun_insentif, $ri);
            }
        }
        else {
            foreach($unit as $u) {
                $ri = RemunInsentif::whereHas('pegawai', function(Builder $query) {
                    return $query->whereIn('status_kerja_id',[2,3]);
                })->where('unit_id','=',$u->id)->where('remun_insentif','>',0)->orderBy('num_order','asc')->get();
                array_push($remun_insentif, $ri);
            }
        }

        // Return
        return Excel::download(new RemunInsentifPusatExport($remun_insentif), 'Remun-15 Pusat ('.($status == 1 ? 'Aktif' : 'Pensiun-MD').').xlsx');
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
        $triwulan = 15;

        // Remun Insentif
        $remun_insentif = RemunInsentif::where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->orderBy('remun_insentif','desc')->get();

        // Return
        return Excel::download(new RemunInsentifRecapExport($remun_insentif), 'Rekap Remun-15 ('.$tahun.').xlsx');
    }
}