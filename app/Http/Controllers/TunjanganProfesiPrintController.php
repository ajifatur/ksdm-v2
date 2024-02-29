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
        $angkatan = Angkatan::whereIn('jenis_id',[1,2,3])->findOrFail($id);

        // Get SK
        if($angkatan->jenis_id == 1) {
            $sk = SK::where('jenis_id','=',2)->where('status','=',1)->whereYear('tanggal',$request->tahun)->first();
            $sk_awal = SK::where('jenis_id','=',2)->where('awal_tahun','=',$request->tahun)->first();
		}
        elseif($angkatan->jenis_id == 2 || $angkatan->jenis_id == 3) {
            $sk = SK::where('jenis_id','=',3)->where('status','=',1)->whereYear('tanggal',$request->tahun)->first();
            $sk_awal = SK::where('jenis_id','=',3)->where('awal_tahun','=',$request->tahun)->first();
		}

        // Get jenis
        $jenis = $angkatan ? JenisTunjanganProfesi::find($angkatan->jenis_id) : null;

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::where('angkatan_id','=',$angkatan->id)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->where('kekurangan','=',0)->get();

        // Get SK dasar
        $sk_dasar = SK::whereHas('tunjangan_profesi', function(Builder $query) use ($request, $angkatan) {
            return $query->where('angkatan_id','=',$angkatan->id)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->where('kekurangan','=',0);
        })->groupBy('id')->get();

        // Set title
        $title = 'Tunjangan '.$angkatan->jenis->nama.' - '.$angkatan->nama.' ('.$request->tahun.' '.DateTimeExt::month($request->bulan).')';
		
		// Set header
        if($request->tahun <= 2023) {
            $header = strtoupper($sk_awal->nama).' TANGGAL '.strtoupper(DateTimeExt::full($sk_awal->tanggal));
        }
        else {
            $header = '';
            foreach($sk_dasar as $key=>$s) {
                $header .= strtoupper($s->nama).' TANGGAL '.strtoupper(DateTimeExt::full($s->tanggal));
                if(count($sk_dasar) > 2 && $key < count($sk_dasar)-1) $header .= ', ';
                if(count($sk_dasar) > 1 && $key == count($sk_dasar)-2) $header .= ' DAN ';
            }
        }

        // PDF
        $pdf = PDF::loadView('admin/tunjangan-profesi/print/single', [
            'title' => $title,
            'header' => $header,
            'jenis' => $jenis,
            'nama' => 'Tunjangan '.$angkatan->jenis->deskripsi,
            'angkatan' => $angkatan,
            'sk' => $sk,
            'sk_awal' => $sk_awal,
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

        if($id == 1) $jenis = 2;
        elseif($id == 2) $jenis = 3;
        elseif($id == 3) $jenis = 3;

        // Get SK
        $sk = SK::where('jenis_id','=',$jenis)->where('status','=',1)->whereYear('tanggal',$request->tahun)->first();
        $sk_awal = SK::where('jenis_id','=',$jenis)->where('awal_tahun','=',$request->tahun)->first();

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) use ($id) {
            return $query->where('jenis_id','=',$id);
        })->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->get();

        // Get SK dasar
        $angkatan = $tunjangan->pluck('angkatan_id')->toArray();
        $sk_dasar = SK::whereHas('tunjangan_profesi', function(Builder $query) use ($request, $angkatan) {
            return $query->whereIn('angkatan_id',$angkatan)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->where('kekurangan','=',0);
        })->groupBy('id')->get();

        // Set title
        $title = 'Tunjangan '.$tunjangan[0]->angkatan->jenis->nama.' ('.$request->tahun.' '.DateTimeExt::month($request->bulan).')';
		
		// Set header
        if($request->tahun <= 2023) {
            $header = strtoupper($sk_awal->nama).' TANGGAL '.strtoupper(DateTimeExt::full($sk_awal->tanggal));
        }
        else {
            $header = '';
            foreach($sk_dasar as $key=>$s) {
                $header .= strtoupper($s->nama).' TANGGAL '.strtoupper(DateTimeExt::full($s->tanggal));
                if(count($sk_dasar) > 2 && $key < count($sk_dasar)-1) $header .= ', ';
                if(count($sk_dasar) > 1 && $key == count($sk_dasar)-2) $header .= ' DAN ';
            }
        }

        // Get jenis
        $jenis = JenisTunjanganProfesi::find($id);

        // PDF
        $pdf = PDF::loadView('admin/tunjangan-profesi/print/single', [
            'title' => $title,
            'header' => $header,
            'jenis' => $jenis,
            'nama' => 'Tunjangan '.$tunjangan[0]->angkatan->jenis->deskripsi,
            'sk' => $sk,
            'sk_awal' => $sk_awal,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'tanggal' => $tanggal,
            'tunjangan' => $tunjangan
        ]);
        $pdf->setPaper([0, 0 , 935, 612]);
        return $pdf->stream($title.'.pdf');
    }

    /**
     * Non PNS.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  id
     * @return \Illuminate\Http\Response
     */
    public function nonPNS(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Set tanggal
        $tanggal = $request->tahun.'-'.($request->bulan < 10 ? '0'.$request->bulan : $request->bulan).'-01';

        // Get SK
        $sk = SK::where('jenis_id','=',4)->where('status','=',1)->whereYear('tanggal',$request->tahun)->first();
        $sk_awal = SK::where('jenis_id','=',4)->where('awal_tahun','=',$request->tahun)->first();

        // Get jenis
        $jenis = JenisTunjanganProfesi::find(4);

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) {
            return $query->where('jenis_id','=',4);
        })->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->get();

        // Get SK dasar
        $angkatan = $tunjangan[0]->angkatan;
        $sk_dasar = SK::whereHas('tunjangan_profesi', function(Builder $query) use ($request, $angkatan) {
            return $query->where('angkatan_id','=',$angkatan->id)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->where('kekurangan','=',0);
        })->groupBy('id')->get();

        // Set title
        $title = 'Tunjangan '.$tunjangan[0]->angkatan->jenis->nama.' ('.$request->tahun.' '.DateTimeExt::month($request->bulan).')';
		
		// Set header
        if($request->tahun <= 2023) {
            $header = strtoupper($sk_awal->nama).' TANGGAL '.strtoupper(DateTimeExt::full($sk_awal->tanggal));
        }
        else {
            $header = '';
            foreach($sk_dasar as $key=>$s) {
                $header .= strtoupper($s->nama).' TANGGAL '.strtoupper(DateTimeExt::full($s->tanggal));
                if(count($sk_dasar) > 2 && $key < count($sk_dasar)-1) $header .= ', ';
                if(count($sk_dasar) > 1 && $key == count($sk_dasar)-2) $header .= ' DAN ';
            }
        }

        // PDF
        $pdf = PDF::loadView('admin/tunjangan-profesi/print/single', [
            'title' => $title,
            'header' => $header,
            'jenis' => $jenis,
            'nama' => 'Tunjangan '.count($tunjangan) > 0 ? $tunjangan[0]->angkatan->jenis->deskripsi : '',
            'sk' => $sk,
            'sk_awal' => $sk_awal,
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
	        $angkatan = Angkatan::whereIn('jenis_id',[1,2,3])->find($request->query('angkatan'));
			
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