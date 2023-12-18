<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Exports\RemunGajiKekuranganExport;
use App\Exports\RemunGajiKekuranganPusatExport;
use App\Exports\RemunGajiKekuranganRecapExport;
use App\Models\RemunGaji;
use App\Models\LebihKurang;
use App\Models\Unit;
use App\Models\Pegawai;
use App\Models\Mutasi;

class RemunGajiKekuranganExportController extends Controller
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

        // Get kategori, bulan, dan tahun
        $kategori = $request->query('kategori');
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        // Set rentang bulan
        if($bulan == 4 && $tahun == 2023)
            $rentang_bulan = 'Januari sampai Maret';
        else
            $rentang_bulan = DateTimeExt::month($bulan);

        // Get unit
        $unit = Unit::find($request->query('unit'));
        if($unit)
            $pegawai_dalam_unit = RemunGaji::where('unit_id','=',$unit->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();

        // Get kekurangan
        if($unit) {
            $kekurangan = LebihKurang::whereHas('pegawai', function(Builder $query) use ($kategori, $pegawai_dalam_unit) {
                return $query->where('jenis','=',$kategori)->whereIn('id',$pegawai_dalam_unit);
            })->where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',1)->groupBy('pegawai_id')->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
        }
        else {
            $kekurangan = LebihKurang::whereHas('pegawai', function(Builder $query) use ($kategori) {
                return $query->where('jenis','=',$kategori);
            })->where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',1)->groupBy('pegawai_id')->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
        }

        foreach($kekurangan as $key=>$k) {
            $kekurangan[$key]->mutasi = Mutasi::whereHas('jenis', function(Builder $query) {
                return $query->where('remun','=',1);
            })->where('pegawai_id','=',$k->pegawai_id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();
            $kekurangan[$key]->grade = $kekurangan[$key]->mutasi && $kekurangan[$key]->mutasi->detail()->where('status','=',1)->first()->jabatan_dasar ? $kekurangan[$key]->mutasi->detail()->where('status','=',1)->first()->jabatan_dasar->grade : 0;
            if($bulan == 4 && $tahun == 2023) {
                $kekurangan[$key]->remun_gaji = RemunGaji::where('pegawai_id','=',$k->pegawai_id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();
                $kekurangan[$key]->grade = $kekurangan[$key]->remun_gaji->jabatan_dasar->grade;
            }
            $kekurangan[$key]->total_selisih = LebihKurang::where('pegawai_id','=',$k->pegawai_id)->where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',1)->sum('selisih');
        }

        $kekurangan = $kekurangan->sortByDesc('grade');

        // Set title
        $title = 'Kekurangan Remun Gaji '.($unit ? $unit->nama.' ' : '').($kategori == 1 ? 'Dosen' : 'Tendik').' ('.$tahun.' '.$rentang_bulan.')';

        // Return
        return Excel::download(new RemunGajiKekuranganExport($kekurangan), $title.'.xlsx');
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

        // Get kategori, bulan, dan tahun
        $kategori = $request->query('kategori');
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        // Set rentang bulan
        if($bulan == 4 && $tahun == 2023)
            $rentang_bulan = 'Januari sampai Maret';
        else
            $rentang_bulan = DateTimeExt::month($bulan);

        // Get unit
        $unit = Unit::where('pusat','=',1)->pluck('id')->toArray();
        $pegawai_dalam_unit = RemunGaji::whereIn('unit_id',$unit)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();

        // Get kekurangan
        $kekurangan = LebihKurang::whereHas('pegawai', function(Builder $query) use ($kategori, $pegawai_dalam_unit) {
            return $query->where('jenis','=',$kategori)->whereIn('id',$pegawai_dalam_unit);
        })->where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',1)->groupBy('pegawai_id')->orderBy('tahun','desc')->orderBy('bulan','desc')->get();

        foreach($kekurangan as $key=>$k) {
            $kekurangan[$key]->mutasi = Mutasi::whereHas('jenis', function(Builder $query) {
                return $query->where('remun','=',1);
            })->where('pegawai_id','=',$k->pegawai_id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();
            $kekurangan[$key]->grade = $kekurangan[$key]->mutasi && $kekurangan[$key]->mutasi->detail()->where('status','=',1)->first()->jabatan_dasar ? $kekurangan[$key]->mutasi->detail()->where('status','=',1)->first()->jabatan_dasar->grade : 0;
            if($bulan == 4 && $tahun == 2023) {
                $kekurangan[$key]->remun_gaji = RemunGaji::where('pegawai_id','=',$k->pegawai_id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();
                $kekurangan[$key]->grade = $kekurangan[$key]->remun_gaji->jabatan_dasar->grade;
            }
            $kekurangan[$key]->total_selisih = LebihKurang::where('pegawai_id','=',$k->pegawai_id)->where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',1)->sum('selisih');
        }

        $kekurangan = $kekurangan->sortByDesc('grade');

        // Set title
        $title = 'Kekurangan Remun Gaji Pusat '.($kategori == 1 ? 'Dosen' : 'Tendik').' ('.$tahun.' '.$rentang_bulan.')';

        // Return
        return Excel::download(new RemunGajiKekuranganPusatExport($kekurangan), $title.'.xlsx');
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

        // Get kategori, bulan, dan tahun
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        // Set rentang bulan
        if($bulan == 4 && $tahun == 2023)
            $rentang_bulan = 'Januari sampai Maret';
        else
            $rentang_bulan = DateTimeExt::month($bulan);

        // Get unit
        $unit = Unit::find($request->query('unit'));
        if($unit)
            $pegawai_dalam_unit = RemunGaji::where('unit_id','=',$unit->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();

        // Get kekurangan
        $kekurangan = LebihKurang::where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',1)->groupBy('pegawai_id')->orderBy('tahun','desc')->orderBy('bulan','desc')->get();

        foreach($kekurangan as $key=>$k) {
            $kekurangan[$key]->mutasi = Mutasi::whereHas('jenis', function(Builder $query) {
                return $query->where('remun','=',1);
            })->where('pegawai_id','=',$k->pegawai_id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();
            $kekurangan[$key]->grade = $kekurangan[$key]->mutasi && $kekurangan[$key]->mutasi->detail()->where('status','=',1)->first()->jabatan_dasar ? $kekurangan[$key]->mutasi->detail()->where('status','=',1)->first()->jabatan_dasar->grade : 0;
            if($bulan == 4 && $tahun == 2023) {
                $kekurangan[$key]->remun_gaji = RemunGaji::where('pegawai_id','=',$k->pegawai_id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();
                $kekurangan[$key]->grade = $kekurangan[$key]->remun_gaji->jabatan_dasar->grade;
            }
            $kekurangan[$key]->total_selisih = LebihKurang::where('pegawai_id','=',$k->pegawai_id)->where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',1)->sum('selisih');
        }

        $kekurangan = $kekurangan->sortByDesc('grade');

        // Set title
        $title = 'Rekap Kekurangan Remun Gaji ('.$tahun.' '.$rentang_bulan.')';

        // Return
        return Excel::download(new RemunGajiKekuranganRecapExport($kekurangan), $title.'.xlsx');
    }
}