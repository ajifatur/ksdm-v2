<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use PDF;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\DateTimeExt;
use App\Imports\MutasiImport;
use App\Models\Pegawai;
use App\Models\Mutasi;
use App\Models\MutasiDetail;
use App\Models\Perubahan;
use App\Models\SPKGB;
use App\Models\SK;
use App\Models\JenisMutasi;
use App\Models\Golru;
use App\Models\GajiPokok;
use App\Models\Pejabat;
use App\Models\Unit;

class SPKGBPrintController extends Controller
{
    /**
     * Print Single.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function single(Request $request, $id)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get SPKGB
        $spkgb = SPKGB::has('mutasi')->findOrFail($id);
		
		// Set title
		$title = 'SPKGB '.$spkgb->mutasi->tmt.' a.n. '.$spkgb->nama;
		
        // PDF
        $pdf = PDF::loadView('admin/spkgb/print/single', [
            'spkgb' => $spkgb,
            'title' => $title,
        ]);
        $pdf->setPaper([0, 0 , 612, 935]);
        return $pdf->stream($title.'.pdf');
    }

    /**
     * Print Batch.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function batch(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
		$tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';
        $jenis = $request->query('jenis') ?: 0;

        // Get SPKGB
        if($request->query('tipe') == 1) {
            if($jenis != 0) {
                $spkgb = SPKGB::whereHas('pegawai', function(Builder $query) use ($jenis) {
                    return $query->where('jenis','=',$jenis)->whereHas('status_kepegawaian', function(Builder $query) {
                        return $query->whereIn('nama',['PNS','CPNS']);
                    });
                })->whereHas('mutasi', function(Builder $query) use ($tanggal) {
                    return $query->has('perubahan')->where('tmt','=',$tanggal);
                })->with('unit')->orderBy(
                    Unit::select('num_order')->whereColumn('tbl_spkgb.unit_id', 'tbl_unit.id')
                )->orderBy(
                    Pegawai::select('nama')->whereColumn('tbl_spkgb.pegawai_id', 'tbl_pegawai.id')
                )->get();
            }
            else {
                $spkgb = SPKGB::whereHas('pegawai', function(Builder $query) {
                    return $query->whereHas('status_kepegawaian', function(Builder $query) {
                        return $query->whereIn('nama',['PNS','CPNS']);
                    });
                })->whereHas('mutasi', function(Builder $query) use ($tanggal) {
                    return $query->has('perubahan')->where('tmt','=',$tanggal);
                })->with('unit')->orderBy(
                    Unit::select('num_order')->whereColumn('tbl_spkgb.unit_id', 'tbl_unit.id')
                )->orderBy(
                    Pegawai::select('nama')->whereColumn('tbl_spkgb.pegawai_id', 'tbl_pegawai.id')
                )->get();
            }
        }
        elseif($request->query('tipe') == 2) {
            $spkgb = SPKGB::whereHas('pegawai', function(Builder $query) {
                return $query->whereHas('status_kepegawaian', function(Builder $query) {
                    return $query->whereIn('nama',['BLU','Calon Pegawai Tetap','Pegawai Tetap Non ASN']);
                });
            })->whereHas('mutasi', function(Builder $query) use ($tanggal) {
                return $query->has('perubahan')->where('tmt','=',$tanggal);
            })->with('unit')->orderBy(
                Unit::select('num_order')->whereColumn('tbl_spkgb.unit_id', 'tbl_unit.id')
            )->orderBy(
                Pegawai::select('nama')->whereColumn('tbl_spkgb.pegawai_id', 'tbl_pegawai.id')
            )->get();
        }

        // Set title
        $title = 'Batch SPKGB '.(in_array($jenis, [1,2]) ? $jenis == 1 ? 'Dosen' : 'Tendik' : '').' '.$tahun.' '.DateTimeExt::month($bulan);
		
        // PDF
        $pdf = PDF::loadView('admin/spkgb/print/batch', [
            'spkgb' => $spkgb,
            'title' => $title,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggal' => $tanggal,
        ]);
        $pdf->setPaper([0, 0 , 612, 935]);
        return $pdf->stream($title.'.pdf');
    }

    /**
     * Print Recap.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function recap(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
		$tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';
        $jenis = $request->query('jenis') ?: 0;

        // Get SPKGB
        if($request->query('tipe') == 1) {
            if($jenis != 0) {
                $spkgb = SPKGB::whereHas('pegawai', function(Builder $query) use ($jenis) {
                    return $query->where('jenis','=',$jenis)->whereHas('status_kepegawaian', function(Builder $query) {
                        return $query->whereIn('nama',['PNS','CPNS']);
                    });
                })->whereHas('mutasi', function(Builder $query) use ($tanggal) {
                    return $query->has('perubahan')->where('tmt','=',$tanggal);
                })->with('unit')->orderBy(
                    Unit::select('num_order')->whereColumn('tbl_spkgb.unit_id', 'tbl_unit.id')
                )->orderBy(
                    Pegawai::select('nama')->whereColumn('tbl_spkgb.pegawai_id', 'tbl_pegawai.id')
                )->get();
            }
            else {
                $spkgb = SPKGB::whereHas('pegawai', function(Builder $query) {
                    return $query->whereHas('status_kepegawaian', function(Builder $query) {
                        return $query->whereIn('nama',['PNS','CPNS']);
                    });
                })->whereHas('mutasi', function(Builder $query) use ($tanggal) {
                    return $query->has('perubahan')->where('tmt','=',$tanggal);
                })->with('unit')->orderBy(
                    Unit::select('num_order')->whereColumn('tbl_spkgb.unit_id', 'tbl_unit.id')
                )->orderBy(
                    Pegawai::select('nama')->whereColumn('tbl_spkgb.pegawai_id', 'tbl_pegawai.id')
                )->get();
            }
        }
        elseif($request->query('tipe') == 2) {
            if($jenis != 0) {
                $spkgb = SPKGB::whereHas('pegawai', function(Builder $query) use ($jenis) {
                    return $query->where('jenis','=',$jenis)->whereHas('status_kepegawaian', function(Builder $query) {
                        return $query->whereIn('nama',['BLU','Calon Pegawai Tetap','Pegawai Tetap Non ASN']);
                    });
                })->whereHas('mutasi', function(Builder $query) use ($tanggal) {
                    return $query->has('perubahan')->where('tmt','=',$tanggal);
                })->with('unit')->orderBy(
                    Unit::select('num_order')->whereColumn('tbl_spkgb.unit_id', 'tbl_unit.id')
                )->orderBy(
                    Pegawai::select('nama')->whereColumn('tbl_spkgb.pegawai_id', 'tbl_pegawai.id')
                )->get();
            }
            else {
                $spkgb = SPKGB::whereHas('pegawai', function(Builder $query) {
                    return $query->whereHas('status_kepegawaian', function(Builder $query) {
                        return $query->whereIn('nama',['BLU','Calon Pegawai Tetap','Pegawai Tetap Non ASN']);
                    });
                })->whereHas('mutasi', function(Builder $query) use ($tanggal) {
                    return $query->has('perubahan')->where('tmt','=',$tanggal);
                })->with('unit')->orderBy(
                    Unit::select('num_order')->whereColumn('tbl_spkgb.unit_id', 'tbl_unit.id')
                )->orderBy(
                    Pegawai::select('nama')->whereColumn('tbl_spkgb.pegawai_id', 'tbl_pegawai.id')
                )->get();
            }
        }

        // Set title
        $title = 'Rekap SPKGB '.(in_array($jenis, [1,2]) ? $jenis == 1 ? 'Dosen' : 'Tendik' : '').' '.$tahun.' '.DateTimeExt::month($bulan);
		
        // PDF
        $pdf = PDF::loadView('admin/spkgb/print/recap', [
            'spkgb' => $spkgb,
            'title' => $title,
            'jenis' => $jenis,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tanggal' => $tanggal,
        ]);
        $pdf->setPaper([0, 0 , 612, 935]);
        return $pdf->stream($title.'.pdf');
    }
}