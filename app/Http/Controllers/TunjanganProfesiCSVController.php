<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Exports\TunjanganProfesiCSVExport;
use App\Models\TunjanganProfesi;
use App\Models\JenisTunjanganProfesi;
use App\Models\Angkatan;
use App\Models\Pegawai;
use App\Models\SK;

class TunjanganProfesiCSVController extends Controller
{
    /**
     * Single.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  id
     * @return \Illuminate\Http\Response
     */
    public function single(Request $request, $id)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get angkatan
        $angkatan = Angkatan::findOrFail($id);

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::where('angkatan_id','=',$angkatan->id)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->get();

        // Download
        return Excel::download(new TunjanganProfesiCSVExport($tunjangan), $angkatan->jenis->file.'_'.$angkatan->nama.'_('.$request->tahun.'_'.DateTimeExt::month($request->bulan).').csv', \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * Non PNS.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function nonPNS(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) {
            return $query->where('jenis_id','=',4);
        })->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->get();

        // Download
        return Excel::download(new TunjanganProfesiCSVExport($tunjangan), 'Non-PNS_('.$request->tahun.'_'.DateTimeExt::month($request->bulan).').csv', \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * Batch.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  id
     * @return \Illuminate\Http\Response
     */
    public function batch(Request $request, $id)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get jenis
        $jenis = JenisTunjanganProfesi::findOrFail($id);

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::whereHas('angkatan', function(Builder $query) use ($jenis) {
            return $query->where('jenis_id','=',$jenis->id);
        })->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->get();

        // Download
        return Excel::download(new TunjanganProfesiCSVExport($tunjangan), $jenis->file.'_('.$request->tahun.'_'.DateTimeExt::month($request->bulan).').csv', \Maatwebsite\Excel\Excel::CSV);
    }
}