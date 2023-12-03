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

class TunjanganProfesiBulanController extends Controller
{
    /**
     * Monitoring.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function monitoring(Request $request)
    {
        // Get tahun
        $tahun = $request->query('tahun') ?: date('Y');

        $data = [];
        for($bulan=1; $bulan<=12; $bulan++) {
			$tunjangan_profesi = [];
			
			// Get jenis
			$jenis = JenisTunjanganProfesi::all();
			foreach($jenis as $j) {
				// Get tunjangan
				$tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) use ($j) {
					return $query->where('jenis_id','=',$j->id);
				})->where('bulan','=',$bulan)->where('tahun','=',$tahun)->get();
				
				// Push to array
				$tunjangan_profesi[strtolower(str_replace('-','_',$j->file))] = [
					'jenis' => $j,
					'pegawai' => $tunjangan->count(),
					'tunjangan' => $tunjangan->sum('tunjangan'),
					'diterimakan' => $tunjangan->sum('diterimakan'),
				];
			}
			
			// Push to array
			array_push($data, [
				'bulan' => $bulan,
				'bulan_nama' => DateTimeExt::month($bulan),
				'tunjangan_profesi' => $tunjangan_profesi
			]);
		}
		
		$total_tunjangan = [];
		$total_diterimakan = [];
		foreach($jenis as $j) {
            // Get tunjangan dan diterimakan
            $tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) use ($j) {
                return $query->where('jenis_id','=',$j->id);
            })->where('tahun','=',$tahun)->sum('tunjangan');
            $diterimakan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) use ($j) {
                return $query->where('jenis_id','=',$j->id);
            })->where('tahun','=',$tahun)->sum('diterimakan');

            // Push to array
            array_push($total_tunjangan, $tunjangan);
            array_push($total_diterimakan, $diterimakan);
		}

        // View
        return view('admin/tunjangan-profesi/bulan/monitoring', [
            'tahun' => $tahun,
            'data' => $data,
            'total_tunjangan' => $total_tunjangan,
            'total_diterimakan' => $total_diterimakan,
        ]);
    }
}