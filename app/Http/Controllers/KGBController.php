<?php

namespace App\Http\Controllers;

use Auth;;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Models\Pegawai;
use App\Models\Mutasi;

class KGBController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {		
		// Get TMT
		$tmt = Mutasi::whereHas('jenis', function(Builder $query) {
			return $query->where('nama','=','KGB');
		})->orderBy('tmt','desc')->groupBy('tmt')->pluck('tmt')->toArray();

        // Get mutasi KP
        $mutasi = Mutasi::whereHas('jenis', function(Builder $query) {
            return $query->where('nama','=','KGB');
        })->where('tmt','=',in_array($request->query('tmt'),$tmt) ? $request->query('tmt') : $tmt[0])->get();

        // View
        return view('admin/kgb/index', [
            'mutasi' => $mutasi,
			'tmt' => $tmt
        ]);
    }

    /**
     * Monitoring.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function monitoring(Request $request)
    {
		// Get TMT
		$tmt = Mutasi::whereHas('jenis', function(Builder $query) {
			return $query->where('nama','=','KGB');
		})->orderBy('tmt','desc')->groupBy('tmt')->pluck('tmt')->toArray();
		
		// Get data
        $data = [];
        $total = 0;
        foreach($tmt as $t) {
            // Get KP dosen
            $dosen = Mutasi::whereHas('jenis', function(Builder $query) {
				return $query->where('nama','=','KGB');
			})->whereHas('pegawai', function(Builder $query) {
				return $query->where('jenis','=',1);
			})->where('tmt','=',$t)->count();
			
            // Get KP tendik
            $tendik = Mutasi::whereHas('jenis', function(Builder $query) {
				return $query->where('nama','=','KGB');
			})->whereHas('pegawai', function(Builder $query) {
				return $query->where('jenis','=',2);
			})->where('tmt','=',$t)->count();

            // Increment total
            $total += ($dosen + $tendik);

            // Push to array
            array_push($data, [
                'tmt' => $t,
                'nama' => DateTimeExt::full($t),
                'dosen' => $dosen,
                'tendik' => $tendik,
                'total' => $dosen + $tendik
            ]);
        }

        // View
        return view('admin/kgb/monitoring', [
            'tmt' => $tmt,
            'data' => $data,
            'total' => $total
        ]);
    }
}
