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
        // Get jenis tunjangan
        $jenis_tunjangan = JenisTunjanganProfesi::all();

        // Get tahun
        $tahun = TunjanganProfesi::where('kekurangan','=',1)->orderBy('tahun','desc')->orderBy('bulan','desc')->groupBy('tahun')->pluck('tahun')->toArray();

        // Set periode, data, total
        $periode = [];
        $data = [];
        $total['pegawai'] = 0; $total['tunjangan'] = 0; $total['pph'] = 0; $total['diterimakan'] = 0;
        foreach($tahun as $t) {
            $bulan = TunjanganProfesi::where('kekurangan','=',1)->where('tahun','=',$t)->orderBy('tahun','desc')->orderBy('bulan','desc')->groupBy('bulan')->pluck('bulan')->toArray();
            array_push($periode, [
                'tahun' => $t,
                'bulan' => $bulan
            ]);

            foreach($bulan as $b) {
                foreach($jenis_tunjangan as $j) {
                    // Count tunjangan by jenis
                    $tunjangan_jenis = TunjanganProfesi::whereHas('angkatan', function(Builder $query) use ($j) {
                        return $query->where('jenis_id','=',$j->id);
                    })->where('kekurangan','=',1)->where('tahun','=',$t)->where('bulan','=',$b)->count();

                    if($tunjangan_jenis > 0) {
                        // Count pegawai
                        $pegawai = count(TunjanganProfesi::where('kekurangan','=',1)->where('tahun','=',$t)->where('bulan','=',$b)->groupBy('pegawai_id')->get());

                        // Sum tunjangan, pph, diterimakan
                        $tunjangan = TunjanganProfesi::where('kekurangan','=',1)->where('tahun','=',$t)->where('bulan','=',$b)->sum('tunjangan');
                        $pph = TunjanganProfesi::where('kekurangan','=',1)->where('tahun','=',$t)->where('bulan','=',$b)->sum('pph');
                        $diterimakan = TunjanganProfesi::where('kekurangan','=',1)->where('tahun','=',$t)->where('bulan','=',$b)->sum('diterimakan');

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
            }
        }

        // View
        return view('admin/tunjangan-profesi/kekurangan/monitoring', [
            'periode' => $periode,
            'data' => $data,
            'total' => $total,
        ]);
    }
}