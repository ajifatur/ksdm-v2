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

class TunjanganProfesiUnitController extends Controller
{
    /**
     * Monitoring.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function monitoring(Request $request)
    {
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-10'; // Maks tanggal 10

        // Get unit
        $unit = Unit::where(function($query) use ($tanggal) {
			$query->where('start_date','<=',$tanggal)->orWhereNull('start_date');
		})->where(function($query) use ($tanggal) {
			$query->where('end_date','>=',$tanggal)->orWhereNull('end_date');
		})->where('pusat','=',0)->whereNotIn('nama',['-','Sekolah Pascasarjana','Pascasarjana'])->orderBy('num_order','asc')->get();

        $data = [];
        foreach($unit as $u) {
			$tunjangan_profesi = [];
			
			// Get jenis
			$jenis = JenisTunjanganProfesi::all();
			foreach($jenis as $j) {
				// Get tunjangan
				$tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) use ($j) {
					return $query->where('jenis_id','=',$j->id);
				})->where('unit_id','=',$u->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->get();
				
				// Push to array
				$tunjangan_profesi[strtolower(str_replace('-','_',$j->file))] = [
					'jenis' => $j,
					'pegawai' => $tunjangan->count(),
					'diterimakan' => $tunjangan->sum('diterimakan'),
				];
			}
			
			// Push to array
			array_push($data, [
				'unit' => $u,
				'tunjangan_profesi' => $tunjangan_profesi
			]);
		}
		
		$total = [];
		foreach($jenis as $j) {
			// Get tunjangan
			$tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) use ($j) {
				return $query->where('jenis_id','=',$j->id);
			})->where('bulan','=',$bulan)->where('tahun','=',$tahun)->sum('diterimakan');

			// Push to array
			array_push($total, $tunjangan);
		}

        // View
        return view('admin/tunjangan-profesi/unit/monitoring', [
            'unit' => $unit,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'data' => $data,
            'total' => $total,
        ]);
    }

    /**
     * Export to Excel.
     *
     * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function excel(Request $request, $id)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");
		
		// Get unit
		$unit = Unit::findOrFail($id);

        // Get jenis
        $jenis = JenisTunjanganProfesi::find($request->query('jenis'));

        if($jenis) {
            // Get tunjangan profesi
            $tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) use ($jenis) {
                return $query->where('jenis_id','=',$jenis->id);
            })->where('unit_id','=',$unit->id)->where('bulan','=',$request->query('bulan'))->where('tahun','=',$request->query('tahun'))->orderBy('pegawai_id','asc')->orderBy('angkatan_id','asc')->get();

            // Download
            return Excel::download(new TunjanganProfesiUnitExport($tunjangan), 'Tunjangan Profesi '.$unit->nama.' ('.$jenis->nama.') - '.$request->tahun.' '.DateTimeExt::month($request->bulan).'.xlsx');
        }
        else {
            // Get tunjangan profesi
            $tunjangan = TunjanganProfesi::where('unit_id','=',$unit->id)->where('bulan','=',$request->query('bulan'))->where('tahun','=',$request->query('tahun'))->orderBy('pegawai_id','asc')->orderBy('angkatan_id','asc')->get();

            // Download
            return Excel::download(new TunjanganProfesiUnitExport($tunjangan), 'Tunjangan Profesi '.$unit->nama.' - '.$request->tahun.' '.DateTimeExt::month($request->bulan).'.xlsx');
        }
    }
}