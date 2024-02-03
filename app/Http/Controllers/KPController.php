<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Models\Pegawai;
use App\Models\Mutasi;

class KPController extends Controller
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
			return $query->where('nama','=','Mutasi Pangkat');
		})->orderBy('tmt','desc')->groupBy('tmt')->pluck('tmt')->toArray();

        // Get mutasi KP
        $mutasi = Mutasi::whereHas('jenis', function(Builder $query) {
            return $query->where('nama','=','Mutasi Pangkat');
        })->where('tmt','=',in_array($request->query('tmt'),$tmt) ? $request->query('tmt') : $tmt[0])->get();

        // View
        return view('admin/kp/index', [
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
			return $query->where('nama','=','Mutasi Pangkat');
		})->orderBy('tmt','desc')->groupBy('tmt')->pluck('tmt')->toArray();
		
		// Get data
        $data = [];
        $total['dosen_asn'] = 0;
        $total['tendik_asn'] = 0;
        $total['asn'] = 0;
        $total['dosen_non_asn'] = 0;
        $total['tendik_non_asn'] = 0;
        $total['non_asn'] = 0;
        foreach($tmt as $t) {
            // Get KP dosen ASN
            $dosen_asn = Mutasi::whereHas('jenis', function(Builder $query) {
				return $query->where('nama','=','Mutasi Pangkat');
			})->whereHas('pegawai', function(Builder $query) {
				return $query->whereHas('status_kepegawaian', function(Builder $query) {
                    return $query->whereIn('nama', ['PNS','CPNS']);
                })->where('jenis','=',1);
			})->where('tmt','=',$t)->count();
			
            // Get KP tendik ASN
            $tendik_asn = Mutasi::whereHas('jenis', function(Builder $query) {
				return $query->where('nama','=','Mutasi Pangkat');
			})->whereHas('pegawai', function(Builder $query) {
				return $query->whereHas('status_kepegawaian', function(Builder $query) {
                    return $query->whereIn('nama', ['PNS','CPNS']);
                })->where('jenis','=',2);
			})->where('tmt','=',$t)->count();

            // Get KP dosen non ASN
            $dosen_non_asn = Mutasi::whereHas('jenis', function(Builder $query) {
				return $query->where('nama','=','Mutasi Pangkat');
			})->whereHas('pegawai', function(Builder $query) {
				return $query->whereHas('status_kepegawaian', function(Builder $query) {
                    return $query->whereIn('nama', ['BLU','Calon Pegawai Tetap','Pegawai Tetap Non ASN']);
                })->where('jenis','=',1);
			})->where('tmt','=',$t)->count();
			
            // Get KP tendik non ASN
            $tendik_non_asn = Mutasi::whereHas('jenis', function(Builder $query) {
				return $query->where('nama','=','Mutasi Pangkat');
			})->whereHas('pegawai', function(Builder $query) {
				return $query->whereHas('status_kepegawaian', function(Builder $query) {
                    return $query->whereIn('nama', ['BLU','Calon Pegawai Tetap','Pegawai Tetap Non ASN']);
                })->where('jenis','=',2);
			})->where('tmt','=',$t)->count();

            // Increment total
            $total['dosen_asn'] += $dosen_asn;
            $total['tendik_asn'] += $tendik_asn;
            $total['asn'] += ($dosen_asn + $tendik_asn);
            $total['dosen_non_asn'] += $dosen_non_asn;
            $total['tendik_non_asn'] += $tendik_non_asn;
            $total['non_asn'] += ($dosen_non_asn + $tendik_non_asn);

            // Push to array
            array_push($data, [
                'tmt' => $t,
                'nama' => DateTimeExt::full($t),
                'dosen_asn' => $dosen_asn,
                'tendik_asn' => $tendik_asn,
                'total_asn' => $dosen_asn + $tendik_asn,
                'dosen_non_asn' => $dosen_non_asn,
                'tendik_non_asn' => $tendik_non_asn,
                'total_non_asn' => $dosen_non_asn + $tendik_non_asn,
            ]);
        }

        // View
        return view('admin/kp/monitoring', [
            'tmt' => $tmt,
            'data' => $data,
            'total' => $total
        ]);
    }
}
