<?php

namespace App\Http\Controllers;

use Auth;
use PDF;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\RemunInsentif;
use App\Models\Unit;

class RemunInsentifPrintController extends Controller
{
    /**
     * Potongan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function potongan(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        $kategori = $request->query('kategori');
        $unit = $request->query('unit');
        $pusat = $request->query('pusat');
        $triwulan = $request->query('triwulan');
        $tahun = $request->query('tahun');

        // Get kategori
        $get_kategori = $kategori == 1 ? 'Dosen' : 'Tendik';

        if($pusat != 1) {
            // Get unit
            $unit = Unit::findOrFail($request->query('unit'));

            // Get pegawai dalam unit berdasarkan remun insentif
            if($kategori == 1)
                $remun_insentif = RemunInsentif::where('unit_id','=',$request->query('unit'))->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[1,3])->orderBy('num_order','desc')->get();
            elseif($kategori == 2)
                $remun_insentif = RemunInsentif::where('unit_id','=',$request->query('unit'))->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[2])->orderBy('num_order','desc')->get();
        }
        else {
            // Get unit pusat
            $unit = Unit::where('pusat','=',1)->pluck('id')->toArray();

            // Get pegawai dalam unit berdasarkan remun insentif
            if($kategori == 1)
                $remun_insentif = RemunInsentif::whereIn('unit_id',$unit)->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[1,3])->orderBy('num_order','desc')->get();
            elseif($kategori == 2)
                $remun_insentif = RemunInsentif::whereIn('unit_id',$unit)->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[2])->orderBy('num_order','desc')->get();
        }

        // Get potongan
        $potongan = LebihKurang::whereIn('pegawai_id',$remun_insentif->pluck('pegawai_id')->toArray())->where('triwulan_proses','=',$triwulan)->where('tahun_proses','=',$tahun)->get();

        foreach($potongan as $key=>$p) {
            // Get remun insentif
            $potongan[$key]->remun_insentif = RemunInsentif::where('pegawai_id','=',$p->pegawai_id)->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->first();
        }

        // Set title
        $title = 'Potongan Remun Insentif '.($pusat != 1 ? $unit->nama : 'Pusat').' '.$get_kategori.' ('.$tahun.' Triwulan '.$triwulan.')';

        // PDF
        $pdf = PDF::loadView('admin/remun-insentif/print/potongan', [
            'title' => $title,
            'unit' => $unit,
            'kategori' => $kategori,
            'triwulan' => $triwulan,
            'tahun' => $tahun,
            'remun_insentif' => $remun_insentif,
            'potongan' => $potongan,
        ]);
        $pdf->setPaper([0, 0 , 935, 612]);
        return $pdf->stream($title.'.pdf');
    }

    /**
     * Zakat.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function zakat(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        $pensiun = $request->query('pensiun');
        $unit = $request->query('unit');
        $pusat = $request->query('pusat');
        $triwulan = $request->query('triwulan');
        $tahun = $request->query('tahun');

        // Set tanggal
        $bulan = $triwulan * 3;
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';

        // Set romawi
        $romawi = ['I','II','III','IV'];

        if($pensiun != 1) {
            if($pusat != 1) {
                // Get unit
                $unit = Unit::findOrFail($request->query('unit'));

                // Get pegawai dalam unit berdasarkan remun insentif
                $remun_insentif_dosen = RemunInsentif::whereHas('pegawai', function(Builder $query) use ($tanggal) {
                    return $query->whereNotIn('status_kerja_id',[2,3])->orWhere('tmt_non_aktif','>',$tanggal);
                })->where('unit_id','=',$request->query('unit'))->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[1,3])->where('remun_insentif','>',0)->orderBy('num_order','asc')->get();
                $remun_insentif_tendik = RemunInsentif::whereHas('pegawai', function(Builder $query) use ($tanggal) {
                    return $query->whereNotIn('status_kerja_id',[2,3])->orWhere('tmt_non_aktif','>',$tanggal);
                })->where('unit_id','=',$request->query('unit'))->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[2])->where('remun_insentif','>',0)->orderBy('num_order','asc')->get();
            }
            else {
                // Get unit pusat
                $unit = Unit::where('pusat','=',1)->pluck('id')->toArray();

                // Get pegawai dalam unit berdasarkan remun insentif
                $remun_insentif_dosen = RemunInsentif::whereHas('pegawai', function(Builder $query) use ($tanggal) {
                    return $query->whereNotIn('status_kerja_id',[2,3])->orWhere('tmt_non_aktif','>',$tanggal);
                })->whereIn('unit_id',$unit)->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[1,3])->where('remun_insentif','>',0)->orderBy('num_order','asc')->get();
                $remun_insentif_tendik = RemunInsentif::whereHas('pegawai', function(Builder $query) use ($tanggal) {
                    return $query->whereNotIn('status_kerja_id',[2,3])->orWhere('tmt_non_aktif','>',$tanggal);
                })->whereIn('unit_id',$unit)->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[2])->where('remun_insentif','>',0)->orderBy('num_order','asc')->get();
            }
        }
        else {
            // Get unit
            $unit = null;

            // Get pegawai dalam unit berdasarkan remun insentif
            $remun_insentif_dosen = RemunInsentif::whereHas('pegawai', function(Builder $query) use ($tanggal) {
                return $query->whereIn('status_kerja_id',[2])->where('tmt_non_aktif','<=',$tanggal);
            })->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[1,3])->where('remun_insentif','>',0)->orderBy('num_order','asc')->get();
            $remun_insentif_tendik = RemunInsentif::whereHas('pegawai', function(Builder $query) use ($tanggal) {
                return $query->whereIn('status_kerja_id',[2])->where('tmt_non_aktif','<=',$tanggal);
            })->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->whereIn('kategori',[2])->where('remun_insentif','>',0)->orderBy('num_order','asc')->get();
        }

        // Set title
        if($pensiun != 1)
            $title = 'Potongan Zakat '.($pusat != 1 ? $unit->nama : 'Pusat').' ('.$tahun.' Triwulan '.$triwulan.')';
        else
            $title = 'Potongan Zakat Pegawai Pensiun ('.$tahun.' Triwulan '.$triwulan.')';

        // PDF
        $pdf = PDF::loadView('admin/remun-insentif/print/zakat', [
            'title' => $title,
            'pensiun' => $pensiun,
            'unit' => $unit,
            'triwulan' => $triwulan,
            'tahun' => $tahun,
            'romawi' => $romawi,
            'remun_insentif_dosen' => $remun_insentif_dosen,
            'remun_insentif_tendik' => $remun_insentif_tendik,
        ]);
        $pdf->setPaper('A4');
        return $pdf->stream($title.'.pdf');
    }

    /**
     * Kwitansi Zakat.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function kwitansi(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        $pensiun = $request->query('pensiun');
        $unit = $request->query('unit');
        $pusat = $request->query('pusat');
        $triwulan = $request->query('triwulan');
        $tahun = $request->query('tahun');

        // Set tanggal
        $bulan = $triwulan * 3;
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';

        // Set romawi
        $romawi = ['I','II','III','IV'];

        if($pensiun != 1) {
            if($pusat != 1) {
                // Get unit
                $unit = Unit::findOrFail($request->query('unit'));

                // Get potongan zakat
                $potongan_zakat = RemunInsentif::whereHas('pegawai', function(Builder $query) use ($tanggal) {
                    return $query->whereNotIn('status_kerja_id',[2,3])->orWhere('tmt_non_aktif','>',$tanggal);
                })->where('unit_id','=',$request->query('unit'))->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->where('remun_insentif','>',0)->get();
            }
            else {
                // Get unit pusat
                $unit = Unit::where('pusat','=',1)->pluck('id')->toArray();

                // Get potongan zakat
                $potongan_zakat = RemunInsentif::whereHas('pegawai', function(Builder $query) use ($tanggal) {
                    return $query->whereNotIn('status_kerja_id',[2,3])->orWhere('tmt_non_aktif','>',$tanggal);
                })->whereIn('unit_id',$unit)->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->where('remun_insentif','>',0)->get();
            }
        }
        else {
            // Get unit
            $unit = null;

            // Get potongan zakat
            $potongan_zakat = RemunInsentif::whereHas('pegawai', function(Builder $query) use ($tanggal) {
                return $query->whereIn('status_kerja_id',[2])->where('tmt_non_aktif','<=',$tanggal);
            })->whereIn('triwulan',[1,2,3,4])->where('triwulan','=',$triwulan)->where('tahun','=',$tahun)->where('remun_insentif','>',0)->get();
        }

        // Set title
        if($pensiun != 1)
            $title = 'Kwitansi Zakat '.($pusat != 1 ? $unit->nama : 'Pusat').' ('.$tahun.' Triwulan '.$triwulan.')';
        else
            $title = 'Kwitansi Zakat Pegawai Pensiun ('.$tahun.' Triwulan '.$triwulan.')';

        // PDF
        $pdf = PDF::loadView('admin/remun-insentif/print/kwitansi', [
            'title' => $title,
            'pensiun' => $pensiun,
            'pusat' => $pusat,
            'unit' => $unit,
            'triwulan' => $triwulan,
            'tahun' => $tahun,
            'romawi' => $romawi,
            'potongan_zakat' => $potongan_zakat,
        ]);
        $pdf->setPaper([0, 0, 612, 935]);
        return $pdf->stream($title.'.pdf');
    }
}
