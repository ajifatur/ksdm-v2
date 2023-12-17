<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use PDF;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Imports\RemunGajiImport;
use App\Models\RemunGaji;
use App\Models\Unit;
use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\SubJabatan;
use App\Models\Pegawai;
use App\Models\Proses;
use App\Models\LebihKurang;
use App\Models\Mutasi;
use App\Models\MutasiDetail;
use App\Models\SK;
use App\Models\StatusKepegawaian;
use App\Models\Referensi;

class RemunGajiKekuranganController extends Controller
{
    /**
     * Print PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function print(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get kategori, bulan, dan tahun
        $kategori = $request->query('kategori');
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        // Set rentang bulan
        if($bulan == 4 && $tahun == 2023)
            $rentang_bulan = 'Januari sampai Maret';
        else
            $rentang_bulan = DateTimeExt::month($bulan);

        // Get unit
        $unit = Unit::find($request->query('unit'));

        // Set total
        $total['terbayar'] = 0;
        $total['seharusnya'] = 0;
        $total['selisih'] = 0;
        $total['selisih_plus'] = 0;

        // Get kekurangan
        $kekurangan = LebihKurang::whereHas('pegawai', function(Builder $query) use ($kategori) {
            return $query->where('jenis','=',$kategori);
        })->where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',1)->groupBy('pegawai_id')->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
        foreach($kekurangan as $key=>$k) {
            $kekurangan[$key]->mutasi = Mutasi::whereHas('jenis', function(Builder $query) {
                return $query->where('remun','=',1);
            })->where('pegawai_id','=',$k->pegawai_id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();
            $kekurangan[$key]->detail = LebihKurang::where('pegawai_id','=',$k->pegawai_id)->where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',1)->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
            $kekurangan[$key]->total_terbayar = LebihKurang::where('pegawai_id','=',$k->pegawai_id)->where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',1)->sum('terbayar');
            $kekurangan[$key]->total_seharusnya = LebihKurang::where('pegawai_id','=',$k->pegawai_id)->where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',1)->sum('seharusnya');
            $kekurangan[$key]->total_selisih = LebihKurang::where('pegawai_id','=',$k->pegawai_id)->where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',1)->sum('selisih');

            // Sum total
            $total['terbayar'] += $kekurangan[$key]->total_terbayar;
            $total['seharusnya'] += $kekurangan[$key]->total_seharusnya;
            $total['selisih'] += $kekurangan[$key]->total_selisih;

            if($kekurangan[$key]->total_selisih >= 0)
                $total['selisih_plus'] += $kekurangan[$key]->total_selisih;
        }

        // Set title
        $title = 'Kekurangan Remun Gaji '.($unit ? $unit->nama.' ' : '').($kategori == 1 ? 'Dosen' : 'Tendik').' ('.$tahun.' '.$rentang_bulan.')';

        // PDF
        $pdf = PDF::loadView('admin/remun-gaji/kekurangan/print-2', [
        // return view('admin/remun-gaji/kekurangan/print-2', [
            'title' => $title,
            'unit' => $unit,
            'rentang_bulan' => $rentang_bulan,
            'kategori' => $kategori,
            'kekurangan' => $kekurangan,
            'total' => $total,
        ]);
        $pdf->setPaper([0, 0 , 935, 612]);
        return $pdf->stream($title.'.pdf');
    }
}