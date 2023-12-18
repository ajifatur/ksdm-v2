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
     * Monitoring.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function monitoring(Request $request)
    {
        // Get tahun kekurangan
        $tahun_kekurangan = LebihKurang::where('kekurangan','=',1)->where('triwulan_proses','=',0)->groupBy('tahun_proses')->pluck('tahun_proses')->toArray();

        // Get bulan kekurangan
        $periode = [];
        foreach($tahun_kekurangan as $t) {
            $bulan_kekurangan = LebihKurang::where('kekurangan','=',1)->where('triwulan_proses','=',0)->where('tahun_proses','=',$t)->orderBy('bulan_proses','desc')->groupBy('bulan_proses')->pluck('bulan_proses')->toArray();
            array_push($periode, [
                'tahun' => $t,
                'bulan' => $bulan_kekurangan
            ]);
        }

        // Set tahun, bulan, tanggal
        if($request->query('periode') != null) {
            $explode = explode('-', $request->query('periode'));
            $tahun = $explode[0];
            $bulan = $explode[1];
        }
        else {
            $tahun = $periode[0]['tahun'];
            $bulan = $periode[0]['bulan'][0];
        }
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';

        // Get unit
        $unit = Unit::where(function($query) use ($tanggal) {
			$query->where('start_date','<=',$tanggal)->orWhereNull('start_date');
		})->where(function($query) use ($tanggal) {
			$query->where('end_date','>=',$tanggal)->orWhereNull('end_date');
		})->where('nama','!=','-')->orderBy('num_order','asc')->get();

        $data = [];
        $isPusat = 0;
        foreach($unit as $u) {
            // Append pusat
            if($isPusat == 0 && $u->pusat == 1) {
                array_push($data, [
                    'unit' => 'Pusat',
                ]);
            }

            array_push($data, [
                'unit' => $u,
            ]);

            $isPusat = $u->pusat;
        }

        // View
        return view('admin/remun-gaji/kekurangan/monitoring', [
            'periode' => $periode,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'tanggal' => $tanggal,
            'unit' => $unit,
            'data' => $data,
        ]);
    }

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
        if($unit)
            $pegawai_dalam_unit = RemunGaji::where('unit_id','=',$unit->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->pluck('pegawai_id')->toArray();

        // Set total
        $total['terbayar'] = 0;
        $total['seharusnya'] = 0;
        $total['selisih'] = 0;
        $total['selisih_plus'] = 0;

        // Get kekurangan
        if($unit) {
            $kekurangan = LebihKurang::whereHas('pegawai', function(Builder $query) use ($kategori, $pegawai_dalam_unit) {
                return $query->where('jenis','=',$kategori)->whereIn('id',$pegawai_dalam_unit);
            })->where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',1)->groupBy('pegawai_id')->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
        }
        else {
            $kekurangan = LebihKurang::whereHas('pegawai', function(Builder $query) use ($kategori) {
                return $query->where('jenis','=',$kategori);
            })->where('bulan_proses','=',$bulan)->where('tahun_proses','=',$tahun)->where('triwulan_proses','=',0)->where('kekurangan','=',1)->groupBy('pegawai_id')->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
        }

        foreach($kekurangan as $key=>$k) {
            $kekurangan[$key]->mutasi = Mutasi::whereHas('jenis', function(Builder $query) {
                return $query->where('remun','=',1);
            })->where('pegawai_id','=',$k->pegawai_id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();
            $kekurangan[$key]->grade = $kekurangan[$key]->mutasi && $kekurangan[$key]->mutasi->detail()->where('status','=',1)->first()->jabatan_dasar ? $kekurangan[$key]->mutasi->detail()->where('status','=',1)->first()->jabatan_dasar->grade : 0;
            if($bulan == 4 && $tahun == 2023) {
                $kekurangan[$key]->remun_gaji = RemunGaji::where('pegawai_id','=',$k->pegawai_id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();
                $kekurangan[$key]->grade = $kekurangan[$key]->remun_gaji->jabatan_dasar->grade;
            }
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

        $kekurangan = $kekurangan->sortByDesc('grade');

        // Set title
        $title = 'Kekurangan Remun Gaji '.($unit ? $unit->nama.' ' : '').($kategori == 1 ? 'Dosen' : 'Tendik').' ('.$tahun.' '.$rentang_bulan.')';

        // PDF
        $pdf = PDF::loadView('admin/remun-gaji/kekurangan/print', [
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
