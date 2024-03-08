<?php

namespace App\Http\Controllers;

use Auth;
use PDF;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Models\TunjanganProfesi;
use App\Models\JenisTunjanganProfesi;
use App\Models\Angkatan;
use App\Models\Pegawai;
use App\Models\SK;
use App\Models\TTD;

class TunjanganProfesiPrintController extends Controller
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

        // Set tanggal
        $tanggal = $request->tahun.'-'.($request->bulan < 10 ? '0'.$request->bulan : $request->bulan).'-01';

        // Get angkatan
        $angkatan = Angkatan::whereHas('jenis', function(Builder $query) {
            return $query->whereIn('nama',['Kehormatan Profesor','Profesi GB','Profesi Non GB']);
        })->findOrFail($id);

        // Get SK
        if($angkatan->jenis->nama == 'Kehormatan Profesor') {
            $sk = SK::whereHas('jenis', function(Builder $query) {
                return $query->where('nama','=','Tunjangan Kehormatan Profesor');
            })->where('tmt','<=',$tanggal)->where(function($query) use ($tanggal) {
                $query->where('tmt_non_aktif','>=',$tanggal)->orWhereNull('tmt_non_aktif');
            })->first();
        }
        elseif($angkatan->jenis->nama == 'Profesi GB' || $angkatan->jenis->nama == 'Profesi Non GB') {
            $sk = SK::whereHas('jenis', function(Builder $query) {
                return $query->where('nama','=','Tunjangan Profesi Dosen PNS');
            })->where('tmt','<=',$tanggal)->where(function($query) use ($tanggal) {
                $query->where('tmt_non_aktif','>=',$tanggal)->orWhereNull('tmt_non_aktif');
            })->first();
        }

        // Get jenis
        $jenis = $angkatan ? JenisTunjanganProfesi::find($angkatan->jenis_id) : null;

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::where('angkatan_id','=',$angkatan->id)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->where('kekurangan','=',0)->get();

        // Set title
        $title = 'Tunjangan '.$angkatan->jenis->nama.' - '.$angkatan->nama.' ('.$request->tahun.' '.DateTimeExt::month($request->bulan).')';
		
		// Set header
        $header = strtoupper($sk->nama).' TANGGAL '.strtoupper(DateTimeExt::full($sk->tanggal));

        // PDF
        $pdf = PDF::loadView('admin/tunjangan-profesi/print/single', [
            'title' => $title,
            'header' => $header,
            'jenis' => $jenis,
            'nama' => 'Tunjangan '.$angkatan->jenis->deskripsi,
            'angkatan' => $angkatan,
            'sk' => $sk,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'tanggal' => $tanggal,
            'tunjangan' => $tunjangan
        ]);
        $pdf->setPaper([0, 0 , 935, 612]);
        return $pdf->stream($title.'.pdf');
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

        // Set tanggal
        $tanggal = $request->tahun.'-'.($request->bulan < 10 ? '0'.$request->bulan : $request->bulan).'-01';

        // Get jenis
        $jenis = JenisTunjanganProfesi::findOrFail($id);

        // Get SK
        if($jenis->nama == 'Kehormatan Profesor') {
            $sk = SK::whereHas('jenis', function(Builder $query) {
                return $query->where('nama','=','Tunjangan Kehormatan Profesor');
            })->where('tmt','<=',$tanggal)->where(function($query) use ($tanggal) {
                $query->where('tmt_non_aktif','>=',$tanggal)->orWhereNull('tmt_non_aktif');
            })->first();
        }
        elseif($jenis->nama == 'Profesi GB' || $jenis->nama == 'Profesi Non GB') {
            $sk = SK::whereHas('jenis', function(Builder $query) {
                return $query->where('nama','=','Tunjangan Profesi Dosen PNS');
            })->where('tmt','<=',$tanggal)->where(function($query) use ($tanggal) {
                $query->where('tmt_non_aktif','>=',$tanggal)->orWhereNull('tmt_non_aktif');
            })->first();
        }
        elseif($jenis->nama == 'Profesi Non PNS') {
            $sk = SK::whereHas('jenis', function(Builder $query) {
                return $query->where('nama','=','Tunjangan Profesi Dosen Non PNS');
            })->where('tmt','<=',$tanggal)->where(function($query) use ($tanggal) {
                $query->where('tmt_non_aktif','>=',$tanggal)->orWhereNull('tmt_non_aktif');
            })->first();
        }

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) use ($jenis) {
            return $query->where('jenis_id','=',$jenis->id);
        })->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->where('kekurangan','=',0)->get();

        // Set title
        $title = 'Tunjangan '.$jenis->nama.' ('.$request->tahun.' '.DateTimeExt::month($request->bulan).')';
		
		// Set header
        $header = strtoupper($sk->nama).' TANGGAL '.strtoupper(DateTimeExt::full($sk->tanggal));

        // PDF
        $pdf = PDF::loadView('admin/tunjangan-profesi/print/single', [
            'title' => $title,
            'header' => $header,
            'jenis' => $jenis,
            'nama' => 'Tunjangan '.$jenis->deskripsi,
            'sk' => $sk,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'tanggal' => $tanggal,
            'tunjangan' => $tunjangan
        ]);
        $pdf->setPaper([0, 0 , 935, 612]);
        return $pdf->stream($title.'.pdf');
    }

    /**
     * SPTJM.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sptjm(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");
		
        // Get bulan, tahun, tanggal
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';

		$angkatan = null; $jenis = null;
		if($request->query('angkatan') != null) {
			// Get angkatan
            $angkatan = Angkatan::whereHas('jenis', function(Builder $query) {
                return $query->whereIn('nama',['Kehormatan Profesor','Profesi GB','Profesi Non GB']);
            })->findOrFail($request->query('angkatan'));
			
			// Set title
        	$title = 'SPTJM Tunjangan '.$angkatan->jenis->nama.' - '.$angkatan->nama.' ('.$tahun.' '.DateTimeExt::month($bulan).')';
		}
		elseif($request->query('jenis') != null) {
			// Get jenis tunjangan
			$jenis = JenisTunjanganProfesi::find($request->query('jenis'));
			
			// Set title
			$title = 'SPTJM Tunjangan '.$jenis->nama.' ('.$tahun.' '.DateTimeExt::month($bulan).')';
		}
		
        // PDF
        $pdf = PDF::loadView('admin/tunjangan-profesi/print/sptjm', [
            'title' => $title,
            'angkatan' => $angkatan,
            'jenis' => $jenis,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggal' => $tanggal,
        ]);
        $pdf->setPaper('A4');
        return $pdf->stream($title.'.pdf');
    }
}