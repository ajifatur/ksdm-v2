<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Exports\GajiKontrakExport;
use App\Exports\GajiKontrakListExport;
use App\Exports\GajiKontrakRecapExport;
use App\Models\GajiKontrak;
use App\Models\JenisGaji;
use App\Models\KategoriKontrak;
use App\Models\Pegawai;
use App\Models\SKKontrak;
use App\Models\Unit;

class GajiKontrakExportController extends Controller
{
    /**
     * Single.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function single(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get jenis
        $jenis = JenisGaji::findOrFail($request->query('jenis'));

        // Get kategori kontrak
        $kategori_kontrak = KategoriKontrak::orderBy('num_order','asc')->get();

        // Get bulan, tahun, kategori
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $kategori = in_array($request->query('kategori'), $kategori_kontrak->pluck('id')->toArray()) ? KategoriKontrak::find($request->query('kategori')) : null;

        // Get gaji
        $gaji = [];
        if($kategori != null) {
            $gaji = GajiKontrak::where('jenis_id','=',$jenis->id)->where('kategori_id','=',$kategori->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->orderBy(
                Unit::select('num_order')->whereColumn((new GajiKontrak)->getTable().'.unit_id', (new Unit)->getTable().'.id')
            )->orderBy(
                Pegawai::select('nama')->whereColumn((new GajiKontrak)->getTable().'.pegawai_id', (new Pegawai)->getTable().'.id')
            )->get();
        }

        // Set nama file
        $filename = $jenis->nama.' '.($kategori ? $kategori->nama : '').' - Upload MyKeu ('.$tahun.' '.DateTimeExt::month($bulan).').xlsx';

        // Return
        return Excel::download(new GajiKontrakExport($gaji), $filename);
    }

    /**
     * List.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get jenis
        $jenis = JenisGaji::findOrFail($request->query('jenis'));

        // Get kategori kontrak
        $kategori_kontrak = KategoriKontrak::orderBy('num_order','asc')->get();

        // Get bulan, tahun, kategori
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $kategori = in_array($request->query('kategori'), $kategori_kontrak->pluck('id')->toArray()) ? KategoriKontrak::find($request->query('kategori')) : null;

        // Get gaji
        $gaji = [];
        if($kategori != null) {
            $gaji = GajiKontrak::where('jenis_id','=',$jenis->id)->where('kategori_id','=',$kategori->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->orderBy(
                Unit::select('num_order')->whereColumn((new GajiKontrak)->getTable().'.unit_id', (new Unit)->getTable().'.id')
            )->orderBy(
                Pegawai::select('nama')->whereColumn((new GajiKontrak)->getTable().'.pegawai_id', (new Pegawai)->getTable().'.id')
            )->get();
        }

        // Set nama file
        $filename = $jenis->nama.' '.($kategori ? $kategori->nama : '').' ('.$tahun.' '.DateTimeExt::month($bulan).').xlsx';

        // Return
        return Excel::download(new GajiKontrakListExport([
            'jenis' => $jenis,
            'kategori_kontrak' => $kategori_kontrak,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'kategori' => $kategori,
            'gaji' => $gaji,
        ]), $filename);
    }

    /**
     * Recap.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function recap(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get jenis
        $jenis = JenisGaji::findOrFail($request->query('jenis'));

        // Get kategori kontrak
        $kategori_kontrak = KategoriKontrak::orderBy('num_order','asc')->get();

        // Get bulan, tahun, kategori
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');

        // Get gaji
        $gaji = GajiKontrak::where('jenis_id','=',$jenis->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->orderBy(
            KategoriKontrak::select('num_order')->whereColumn((new GajiKontrak)->getTable().'.kategori_id', (new KategoriKontrak)->getTable().'.id')
        )->orderBy(
            Unit::select('num_order')->whereColumn((new GajiKontrak)->getTable().'.unit_id', (new Unit)->getTable().'.id')
        )->orderBy(
            Pegawai::select('nama')->whereColumn((new GajiKontrak)->getTable().'.pegawai_id', (new Pegawai)->getTable().'.id')
        )->get();

        // Return
        return Excel::download(new GajiKontrakRecapExport($gaji), 'Rekap '.$jenis->nama.' Pegawai Tidak Tetap ('.$tahun.' '.DateTimeExt::month($bulan).').xlsx');
    }
}
