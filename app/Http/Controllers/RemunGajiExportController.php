<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Exports\RemunGajiExport;
use App\Exports\RemunGajiPusatExport;
use App\Exports\RemunGajiRecapExport;
use App\Models\RemunGaji;
use App\Models\Unit;
use App\Models\Pegawai;

class RemunGajiExportController extends Controller
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
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        // Get unit
        $get_unit = Unit::findOrFail($unit);

        // Get kategori
        $get_kategori = $kategori == 1 ? 'Dosen' : 'Tendik';

        // Remun Gaji
        if($tahun < 2024)
            $remun_gaji = RemunGaji::where('unit_id','=',$unit)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kategori','=',$kategori)->orderBy('remun_gaji','desc')->orderBy('status_kepeg_id','asc')->get();
        else
            $remun_gaji = RemunGaji::where('unit_id','=',$unit)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kategori','=',$kategori)->get();

        // Return
        return Excel::download(new RemunGajiExport($remun_gaji), 'Remun Gaji '.$get_unit->nama.' '.$get_kategori.' ('.$tahun.' '.DateTimeExt::month($bulan).').xlsx');
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

        $kategori = $request->query('kategori');
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        // Get kategori
        $get_kategori = $kategori == 1 ? 'Dosen' : 'Tendik';

        // Get unit
        $unit = Unit::where('pusat','=',1)->orderBy('num_order_remun','asc')->get();
        
        // Get remun gaji
        $remun_gaji = [];
        foreach($unit as $u) {
            if($tahun < 2024)
                $rg = RemunGaji::where('unit_id','=',$u->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kategori','=',$kategori)->orderBy('remun_gaji','desc')->orderBy('status_kepeg_id','asc')->get();
            else
                $rg = RemunGaji::where('unit_id','=',$u->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->where('kategori','=',$kategori)->get();

            array_push($remun_gaji, $rg);
        }

        // Return
        return Excel::download(new RemunGajiPusatExport($remun_gaji), 'Remun Gaji Pusat '.$get_kategori.' ('.$tahun.' '.DateTimeExt::month($bulan).').xlsx');
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

        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        // Remun Gaji
        $remun_gaji = RemunGaji::where('bulan','=',$bulan)->where('tahun','=',$tahun)->orderBy('remun_gaji','desc')->orderBy('status_kepeg_id','asc')->get();

        // Return
        return Excel::download(new RemunGajiRecapExport($remun_gaji), 'Rekap Remun Gaji ('.$tahun.' '.DateTimeExt::month($bulan).').xlsx');
    }
}