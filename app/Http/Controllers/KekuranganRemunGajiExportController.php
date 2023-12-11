<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Exports\KekuranganRemunGajiExport;
use App\Exports\KekuranganRemunGajiPusatExport;
use App\Exports\KekuranganRemunGajiRecapExport;
use App\Models\KekuranganRemunGaji;
use App\Models\Unit;
use App\Models\Pegawai;

class KekuranganRemunGajiExportController extends Controller
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

        // Get unit
        $get_unit = Unit::findOrFail($unit);

        // Get kategori
        $get_kategori = $kategori == 1 ? 'Dosen' : 'Tendik';

        // Get kekurangan
        $kekurangan = KekuranganRemunGaji::where('unit_id','=',$unit)->where('kekurangan_id','=',$request->query('id'))->where('kategori','=',$kategori)->where('selisih','>=',0)->orderBy('selisih','desc')->orderBy('status_kepeg_id','asc')->get();

        // Return
        return Excel::download(new KekuranganRemunGajiExport($kekurangan), 'Kekurangan Remun Gaji '.$get_unit->nama.' '.$get_kategori.' (Januari-Maret 2023).xlsx');
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

        // Get kategori
        $get_kategori = $kategori == 1 ? 'Dosen' : 'Tendik';

        // Get unit
        $unit = Unit::where('pusat','=',1)->orderBy('num_order_remun','asc')->get();
        
        // Get kekurangan
        $kekurangan = [];
        foreach($unit as $u) {
            $k = KekuranganRemunGaji::where('unit_id','=',$u->id)->where('kekurangan_id','=',$request->query('id'))->where('kategori','=',$kategori)->where('selisih','>=',0)->orderBy('selisih','desc')->orderBy('status_kepeg_id','asc')->get();
            array_push($kekurangan, $k);
        }

        // Return
        return Excel::download(new KekuranganRemunGajiPusatExport($kekurangan), 'Kekurangan Remun Gaji Pusat '.$get_kategori.' (Januari-Maret 2023).xlsx');
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

        // Get kekurangan
        $kekurangan = KekuranganRemunGaji::where('kekurangan_id','=',$request->query('id'))->where('selisih','>=',0)->orderBy('selisih','desc')->orderBy('status_kepeg_id','asc')->get();

        // Return
        return Excel::download(new KekuranganRemunGajiRecapExport($kekurangan), 'Rekap Kekurangan Remun Gaji (Januari-Maret 2023).xlsx');
    }
}