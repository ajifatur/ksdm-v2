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

        // Get bendahara pengeluaran
        $bendahara_pengeluaran = TTD::where('kode','=','bpeng')->where('tanggal_mulai','<=',$tanggal)->where('tanggal_selesai','>=',$tanggal)->first();

        // Get angkatan
        $angkatan = Angkatan::whereIn('jenis_id',[1,2,3])->findOrFail($id);

        // Get SK
        if($angkatan->jenis_id == 1)
            $sk = SK::where('jenis_id','=',2)->where('status','=',1)->whereYear('tanggal',$request->tahun)->first();
            // $sk = SK::where('jenis_id','=',2)->where('awal_tahun','=',$request->tahun)->first();
        elseif($angkatan->jenis_id == 2 || $angkatan->jenis_id == 3)
            $sk = SK::where('jenis_id','=',3)->where('status','=',1)->whereYear('tanggal',$request->tahun)->first();
            // $sk = SK::where('jenis_id','=',3)->where('awal_tahun','=',$request->tahun)->first();

        // Get jenis
        $jenis = $angkatan ? JenisTunjanganProfesi::find($angkatan->jenis_id) : null;

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::where('angkatan_id','=',$angkatan->id)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->get();

        // Set title
        $title = 'Tunjangan '.$angkatan->jenis->nama.' - '.$angkatan->nama.' ('.$request->tahun.' '.DateTimeExt::month($request->bulan).')';

        // PDF
        $pdf = PDF::loadView('admin/tunjangan-profesi/print/single', [
            'title' => $title,
            'jenis' => $jenis,
            'nama' => 'Tunjangan '.$angkatan->jenis->deskripsi,
            'angkatan' => $angkatan,
            'sk' => $sk,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'tunjangan' => $tunjangan,
            'bendahara_pengeluaran' => $bendahara_pengeluaran,
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

        // Get bendahara pengeluaran
        $bendahara_pengeluaran = TTD::where('kode','=','bpeng')->where('tanggal_mulai','<=',$tanggal)->where('tanggal_selesai','>=',$tanggal)->first();

        if($id == 1) $jenis = 2;
        elseif($id == 2) $jenis = 3;
        elseif($id == 3) $jenis = 3;

        // Get SK
        // $sk = SK::where('jenis_id','=',$jenis)->where('awal_tahun','=',$request->tahun)->first();
        $sk = SK::where('jenis_id','=',$jenis)->where('status','=',1)->whereYear('tanggal',$request->tahun)->first();

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) use ($id) {
            return $query->where('jenis_id','=',$id);
        })->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->get();

        // Set title
        $title = 'Tunjangan '.$tunjangan[0]->angkatan->jenis->nama.' ('.$request->tahun.' '.DateTimeExt::month($request->bulan).')';

        // Get jenis
        $jenis = JenisTunjanganProfesi::find($id);

        // PDF
        $pdf = PDF::loadView('admin/tunjangan-profesi/print/single', [
            'title' => $title,
            'jenis' => $jenis,
            'nama' => 'Tunjangan '.$tunjangan[0]->angkatan->jenis->deskripsi,
            'sk' => $sk,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'tunjangan' => $tunjangan,
            'bendahara_pengeluaran' => $bendahara_pengeluaran,
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

        // Get bendahara pengeluaran
        $bendahara_pengeluaran = TTD::where('kode','=','bpeng')->where('tanggal_mulai','<=',$tanggal)->where('tanggal_selesai','>=',$tanggal)->first();

        // Get SK
        $sk = SK::where('jenis_id','=',4)->where('status','=',1)->whereYear('tanggal',$request->tahun)->first();
        // $sk = SK::where('jenis_id','=',4)->where('status','=',1)->where('awal_tahun','=',$request->tahun)->first();

        // Get jenis
        $jenis = JenisTunjanganProfesi::find(4);

        // Get tunjangan profesi
        $tunjangan = TunjanganProfesi::whereHas('angkatan', function (Builder $query) {
            return $query->where('jenis_id','=',4);
        })->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->get();

        // Set title
        $title = 'Tunjangan '.$tunjangan[0]->angkatan->jenis->nama.' ('.$request->tahun.' '.DateTimeExt::month($request->bulan).')';

        // PDF
        $pdf = PDF::loadView('admin/tunjangan-profesi/print/single', [
            'title' => $title,
            'jenis' => $jenis,
            'nama' => 'Tunjangan '.$tunjangan[0]->angkatan->jenis->deskripsi,
            'sk' => $sk,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'tunjangan' => $tunjangan,
            'bendahara_pengeluaran' => $bendahara_pengeluaran,
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
		
        // Get bulan dan tahun
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');

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
        ]);
        $pdf->setPaper('A4');
        return $pdf->stream($title.'.pdf');
    }
}