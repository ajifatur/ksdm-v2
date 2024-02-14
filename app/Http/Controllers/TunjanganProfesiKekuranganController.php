<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Exports\TunjanganProfesiUnitExport;
use App\Models\TunjanganProfesi;
use App\Models\JenisTunjanganProfesi;
use App\Models\Angkatan;
use App\Models\Pegawai;
use App\Models\Unit;

class TunjanganProfesiKekuranganController extends Controller
{
    /**
     * Monitoring.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function monitoring(Request $request)
    {
        // Set tahun, bulan periode
        $periode_tahun = $request->query('periode') != null ? explode('-',$request->query('periode'))[0] : null;
        $periode_bulan = $request->query('periode') != null ? explode('-',$request->query('periode'))[1] : null;

        // Get jenis tunjangan
        $jenis_tunjangan = JenisTunjanganProfesi::all();

        // Get tahun
        $tahun = TunjanganProfesi::where('kekurangan','=',1)->orderBy('tahun','desc')->orderBy('bulan','desc')->groupBy('tahun')->pluck('tahun')->toArray();

        // Set periode
        $periode = [];
        foreach($tahun as $t) {
            $bulan = TunjanganProfesi::where('kekurangan','=',1)->where('tahun','=',$t)->orderBy('tahun','desc')->orderBy('bulan','desc')->groupBy('bulan')->pluck('bulan')->toArray();

            foreach($bulan as $b) {
                array_push($periode, [
                    'tahun' => $t,
                    'bulan' => $b
                ]);
            }
        }

        // Set tahun, bulan periode
        if($periode_tahun == null && $periode_bulan == null && count($periode) > 0) {
            $periode_tahun = $periode[0]['tahun'];
            $periode_bulan = $periode[0]['bulan'];
        }

        // Set data, total
        $data = [];
        $total['pegawai'] = 0; $total['tunjangan'] = 0; $total['pph'] = 0; $total['diterimakan'] = 0;
        foreach($jenis_tunjangan as $j) {
            // Count tunjangan by jenis
            $tunjangan_jenis = TunjanganProfesi::whereHas('angkatan', function(Builder $query) use ($j) {
                return $query->where('jenis_id','=',$j->id);
            })->where('kekurangan','=',1)->where('tahun','=',$tahun)->where('bulan','=',$bulan)->count();

            if($tunjangan_jenis > 0) {
                // Count pegawai
                $pegawai = count(TunjanganProfesi::where('kekurangan','=',1)->where('tahun','=',$tahun)->where('bulan','=',$bulan)->groupBy('pegawai_id')->get());

                // Sum tunjangan, pph, diterimakan
                $tunjangan = TunjanganProfesi::where('kekurangan','=',1)->where('tahun','=',$tahun)->where('bulan','=',$bulan)->sum('tunjangan');
                $pph = TunjanganProfesi::where('kekurangan','=',1)->where('tahun','=',$tahun)->where('bulan','=',$bulan)->sum('pph');
                $diterimakan = TunjanganProfesi::where('kekurangan','=',1)->where('tahun','=',$tahun)->where('bulan','=',$bulan)->sum('diterimakan');

                // Push to array
                array_push($data, [
                    'jenis' => $j,
                    'pegawai' => $pegawai,
                    'tunjangan' => $tunjangan,
                    'pph' => $pph,
                    'diterimakan' => $diterimakan,
                ]);

                // Increment
                $total['pegawai'] += $pegawai;
                $total['tunjangan'] += $tunjangan;
                $total['pph'] += $pph;
                $total['diterimakan'] += $diterimakan;
            }
        }

        // View
        return view('admin/tunjangan-profesi/kekurangan/monitoring', [
            'periode_tahun' => $periode_tahun,
            'periode_bulan' => $periode_bulan,
            'periode' => $periode,
            'data' => $data,
            'total' => $total,
        ]);
    }
}