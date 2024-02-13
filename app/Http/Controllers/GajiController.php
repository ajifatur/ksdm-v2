<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use PDF;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Ajifatur\Helpers\DateTimeExt;
use Ajifatur\Helpers\FileExt;
use App\Exports\GajiExport;
use App\Imports\ByStartRowImport;
use App\Models\Gaji;
use App\Models\JenisGaji;
use App\Models\AnakSatker;
use App\Models\Pegawai;
use App\Models\PegawaiNonAktif;
use App\Models\SK;

class GajiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $status = $request->query('status');
        $id = $request->query('id') ?: 0;

        // Get jenis
        $jenis = JenisGaji::findOrFail($request->query('jenis'));

        // Get anak satker
        $as = AnakSatker::find($id);

        // Get anak satker
        $anak_satker = AnakSatker::where('jenis','=',$status)->get();

        // Get gaji
        $gaji = [];
        if($id != 0) {
            $gaji = Gaji::whereHas('anak_satker', function(Builder $query) use($status, $as) {
                return $query->where('jenis','=',$status)->where('id','=',$as->id);
            })->where('jenis_id','=',$jenis->id)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->get();
        }

        // View
        return view('admin/gaji/index', [
            'status' => $status,
            'jenis' => $jenis,
            'anak_satker' => $anak_satker,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'gaji' => $gaji,
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
        // Get jenis dan status
        $jenis = JenisGaji::find($request->query('jenis'));
        $status = $request->query('status');

        $tahun_bulan_grup = [];
        if($jenis->grup == 1) {
            // Get tahun grup
            $tahun_grup = Gaji::whereHas('anak_satker', function(Builder $query) use($status) {
                return $query->where('jenis','=',$status);
            })->where('jenis_id','=',$jenis->id)->orderBy('tahun','desc')->groupBy('tahun')->pluck('tahun')->toArray();

            // Get bulan grup
            foreach($tahun_grup as $t) {
                $bulan_grup = Gaji::whereHas('anak_satker', function(Builder $query) use($status) {
                    return $query->where('jenis','=',$status);
                })->where('jenis_id','=',$jenis->id)->where('tahun','=',$t)->orderBy('bulan','desc')->groupBy('bulan')->pluck('bulan')->toArray();
                array_push($tahun_bulan_grup, [
                    'tahun' => $t,
                    'bulan' => $bulan_grup
                ]);
            }

            // Get bulan dan tahun
            if(count($tahun_bulan_grup) > 0) {
                $bulan = $request->query('bulan') ?: (int)$tahun_bulan_grup[0]['bulan'][0];
                $tahun = $request->query('tahun') ?: $tahun_bulan_grup[0]['tahun'];
            }
            else {
                $bulan = $request->query('bulan') ?: date('n');
                $tahun = $request->query('tahun') ?: date('Y');
            }
        }
        elseif($jenis->grup == 0) {
            // Get bulan dan tahun
            $bulan = $request->query('bulan') ?: date('n');
            $tahun = $request->query('tahun') ?: date('Y');
        }

        // Get jenis gaji
        $jenis_gaji = JenisGaji::all();

        // Get anak satker
        $anak_satker = AnakSatker::where('jenis','=',$status)->get();

        $data = [];
        $total = [
            'dosen_jumlah' => 0,
            'dosen_nominal' => 0,
            'dosen_potongan' => 0,
            'tendik_jumlah' => 0,
            'tendik_nominal' => 0,
            'tendik_potongan' => 0,
        ];
        foreach($anak_satker as $a) {
            // Get gaji
            if($jenis) {
                $gaji = Gaji::whereHas('anak_satker', function(Builder $query) use($status, $a) {
                    return $query->where('jenis','=',$status)->where('id','=',$a->id);
                })->where('jenis_id','=',$jenis->id)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->get();
            }
            else {
                $gaji = Gaji::whereHas('anak_satker', function(Builder $query) use($status, $a) {
                    return $query->where('jenis','=',$status)->where('id','=',$a->id);
                })->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->get();
            }


            // Set angka
            $dosen_jumlah = $gaji->where('jenis','=',1)->count();
            $dosen_nominal = $gaji->where('jenis','=',1)->sum('nominal');
            $dosen_potongan = $gaji->where('jenis','=',1)->sum('potongan');
            $tendik_jumlah = $gaji->where('jenis','=',2)->count();
            $tendik_nominal = $gaji->where('jenis','=',2)->sum('nominal');
            $tendik_potongan = $gaji->where('jenis','=',2)->sum('potongan');

            // Push data
            array_push($data, [
                'anak_satker' => $a,
                'dosen_jumlah' => $dosen_jumlah,
                'dosen_nominal' => $dosen_nominal,
                'dosen_potongan' => $dosen_potongan,
                'tendik_jumlah' => $tendik_jumlah,
                'tendik_nominal' => $tendik_nominal,
                'tendik_potongan' => $tendik_potongan,
            ]);

            // Total
            $total['dosen_jumlah'] += $dosen_jumlah;
            $total['dosen_nominal'] += $dosen_nominal;
            $total['dosen_potongan'] += $dosen_potongan;
            $total['tendik_jumlah'] += $tendik_jumlah;
            $total['tendik_nominal'] += $tendik_nominal;
            $total['tendik_potongan'] += $tendik_potongan;
        }

        // View
        return view('admin/gaji/monitoring', [
            'anak_satker' => $anak_satker,
            'jenis' => $jenis,
            'status' => $status,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tahun_bulan_grup' => $tahun_bulan_grup,
            'jenis_gaji' => $jenis_gaji,
            'data' => $data,
            'total' => $total,
        ]);
    }

    /**
     * Monthly Recap.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function monthly(Request $request)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);

        // Get tahun dan status
        $tahun = $request->query('tahun') ?: date('Y');
        $status = $request->query('status') ?: 1;

        // Get jenis
        $jenis = JenisGaji::find($request->query('jenis'));

        // Get jenis gaji
        $jenis_gaji = JenisGaji::all();

        // Get anak satker
        $anak_satker_all = AnakSatker::where('jenis','=',$status)->get();

        // Get anak satker
        $anak_satker = AnakSatker::find($request->query('id'));

        // Get gaji
        $gaji = [];
        if($anak_satker) {
            if($jenis) {
                $gaji = Gaji::whereHas('anak_satker', function(Builder $query) use($status, $anak_satker) {
                    return $query->where('jenis','=',$status)->where('id','=',$anak_satker->id);
                })->where('jenis_id','=',$jenis->id)->where('tahun','=',$tahun)->get();
            }
            else {
                $gaji = Gaji::whereHas('anak_satker', function(Builder $query) use($status, $anak_satker) {
                    return $query->where('jenis','=',$status)->where('id','=',$anak_satker->id);
                })->where('tahun','=',$tahun)->get();
            }
        }

        // Get kategori gaji
        $kategori_gaji = ['gjpokok', 'tjistri', 'tjanak', 'tjupns', 'tjstruk', 'tjfungs', 'tjdaerah', 'tjpencil', 'tjlain', 'tjkompen', 'pembul', 'tjberas', 'tjpph', 'potpfkbul', 'potpfk2', 'potpfk10', 'potpph', 'potswrum', 'potkelbtj', 'potlain', 'pottabrum', 'bpjs', 'bpjs2'];

        // View
        return view('admin/gaji/monthly', [
            'tahun' => $tahun,
            'status' => $status,
            'anak_satker_all' => $anak_satker_all,
            'anak_satker' => $anak_satker,
            'gaji' => $gaji,
            'jenis' => $jenis,
            'jenis_gaji' => $jenis_gaji,
            'kategori_gaji' => $kategori_gaji,
        ]);
    }

    /**
     * Annually Recap.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function annually(Request $request)
    {
		ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "-1");

        // Get status
        $status = $request->query('status');

        // Get tahun
        $tahun = $request->query('tahun') ?: date('Y');

        // Get jenis
        $jenis = JenisGaji::find($request->query('jenis'));

        // Get jenis gaji
        $jenis_gaji = JenisGaji::all();

        // Get anak satker
        $anak_satker = AnakSatker::where('jenis','=',$status)->get();

        // Get gaji
        $gaji = [];
        if($jenis && in_array($request->query('kategori'), [1,2])) {
            $gaji = Gaji::whereHas('anak_satker', function(Builder $query) use ($status) {
                return $query->where('jenis','=',$status);
            })->where('jenis_id','=',$jenis->id)->where('jenis','=',$request->query('kategori'))->where('tahun','=',$tahun)->get();
        }

        // Get kategori gaji
        $kategori_gaji = ['gjpokok', 'tjistri', 'tjanak', 'tjupns', 'tjstruk', 'tjfungs', 'pembul', 'tjberas', 'tjpph'];

        // View
        return view('admin/gaji/annually', [
            'status' => $status,
            'tahun' => $tahun,
            'anak_satker' => $anak_satker,
            'gaji' => $gaji,
            'jenis' => $jenis,
            'jenis_gaji' => $jenis_gaji,
            'kategori_gaji' => $kategori_gaji,
        ]);
    }

    /**
     * Print.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  id
     * @return \Illuminate\Http\Response
     */
    public function print(Request $request, $id)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $kategori = $request->query('kategori');

        // Get jenis
        $jenis = JenisGaji::findOrFail($request->query('jenis'));

        // Get anak satker
        $anak_satker = AnakSatker::find($id);

        // Get gaji
        $gaji = Gaji::where('jenis_id','=',$jenis->id)->where('jenis','=',$kategori)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->where('kdanak','=',$anak_satker->kode)->get();

        // Set title
        $title = $jenis->nama.' '.($anak_satker->jenis == 1 ? 'PNS' : 'PPPK').' '.($anak_satker->jenis == 1 ? $anak_satker->nama : '').' '.($kategori == 1 ? 'Dosen' : 'Tendik').' ('.$tahun.' '.DateTimeExt::month($bulan).')';

        // PDF
        $pdf = PDF::loadView('admin/gaji/print', [
            'title' => $title,
            'jenis' => $jenis,
            'kategori' => $kategori,
            'anak_satker' => $anak_satker,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'gaji' => $gaji,
        ]);
        $pdf->setPaper([0, 0 , 935, 612]);
        return $pdf->stream($title.'.pdf');
    }

    /**
     * Export to Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
		ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "-1");

        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $status = $request->query('status') ?: 1;

        // Get jenis
        $jenis = JenisGaji::findOrFail($request->query('jenis'));

        // Get anak satker
        $anak_satker = AnakSatker::find($request->query('id'));

        // Set kategori
        $kategori = ($request->query('kategori') == 1 || $request->query('kategori') == 2) ? $request->query('kategori') == 1 ? 'Dosen' : 'Tendik' : '';
    
        // Get kategori gaji
        $kategori_gaji = ['gjpokok', 'tjistri', 'tjanak', 'tjupns', 'tjstruk', 'tjfungs', 'tjdaerah', 'tjpencil', 'tjlain', 'tjkompen', 'pembul', 'tjberas', 'tjpph', 'potpfkbul', 'potpfk2', 'potpfk10', 'potpph', 'potswrum', 'potkelbtj', 'potlain', 'pottabrum', 'bpjs', 'bpjs2'];

        // Jika anak satker dan kategori diketahui
        if($anak_satker && $kategori != '') {
            // Get gaji
            $gaji = Gaji::where('jenis_id','=',$jenis->id)->where('kdanak','=',$anak_satker->kode)->where('tahun','=',$tahun)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('jenis','=',$request->query('kategori'))->get();

            // Set nama file
            $filename = $jenis->kode.' '.$anak_satker->nama.' '.$tahun.' '.DateTimeExt::month($bulan).' ('.$kategori.').xlsx';
        }
        // Jika anak satker tidak diketahui dan kategori diketahui
        elseif(!$anak_satker && $kategori != '') {
            // Get gaji
            $gaji = Gaji::whereHas('anak_satker', function(Builder $query) use ($status) {
                return $query->where('jenis','=',$status);
            })->where('jenis_id','=',$jenis->id)->where('tahun','=',$tahun)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('jenis','=',$request->query('kategori'))->get();

            // Set nama file
            $filename = $jenis->kode.' '.($status == 1 ? 'PNS' : 'PPPK').' '.$tahun.' '.DateTimeExt::month($bulan).' ('.$kategori.').xlsx';
        }
        // Jika anak satker dan kategori tidak diketahui
        else {
            // Get gaji
            $gaji = Gaji::whereHas('anak_satker', function(Builder $query) use ($status) {
                return $query->where('jenis','=',$status);
            })->where('jenis_id','=',$jenis->id)->where('tahun','=',$tahun)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->get();

            // Set nama file
            $filename = $jenis->kode.' '.($status == 1 ? 'PNS' : 'PPPK').' '.$tahun.' '.DateTimeExt::month($bulan).'.xlsx';
        }

        if(count($gaji) <= 0) {
            echo "Tidak ada data!";
            return;
        }

        // Return
        return Excel::download(new GajiExport([
            'gaji' => $gaji,
            'kategori' => $kategori,
            'kategori_gaji' => $kategori_gaji,
        ]), $filename);
    }
    
    /**
     * Import
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        if($request->method() == "GET") {
            // Get status
            $status = $request->query('status') ?: 1;

            // View
            return view('admin/gaji/import', [
                'status' => $status
            ]);
        }
        elseif($request->method() == "POST") {
            ini_set("memory_limit", "-1");
            ini_set("max_execution_time", "-1");

            // Get SK
            $sk = SK::where('jenis_id','=',5)->where('status','=',1)->first();
            
            // Make directory if not exists
            if(!File::exists(public_path('storage/spreadsheets/gaji')))
                File::makeDirectory(public_path('storage/spreadsheets/gaji'));

            // Get the file
            $file = $request->file('file');
            $filename = FileExt::info($file->getClientOriginalName())['nameWithoutExtension'];
            $extension = FileExt::info($file->getClientOriginalName())['extension'];
            $new = date('Y-m-d-H-i-s').'_'.$filename.'.'.$extension;

            // Move the file
            $file->move(public_path('storage/spreadsheets/gaji'), $new);

            // Get array
            $array = Excel::toArray(new ByStartRowImport(2), public_path('storage/spreadsheets/gaji/'.$new));

            $jenis = '';
            $anak_satker = '';
            $bulan = '';
            $bulanAngka = '';
            $tahun = '';
            if(count($array)>0) {
                foreach($array[0] as $key=>$data) {
                    if($data[1] != null) {
                        if($data[6] != 8) {
                            // Import GPP Baru
                            if($data[0] == $request->satker) {
                                // Get jenis, anak satker, bulan, tahun
                                if($key == 0) {
                                    $jenis = JenisGaji::where('kode2','=',$data[6])->first();
                                    $a = AnakSatker::where('kode','=',$data[1])->first();
                                    $anak_satker = $a->nama;
                                    $bulan = DateTimeExt::month((int)$data[3]);
                                    $bulanAngka = (int)$data[3];
                                    $tahun = $data[4];
                                }

                                // Get pegawai
                                $pegawai = Pegawai::where('nip','=',$data[7])->first();

                                // Jika pegawai tidak ditemukan
                                if(!$pegawai) {
                                    // Get pegawai non aktif
                                    $pegawai_non_aktif = PegawaiNonAktif::where('nip','=',$data[7])->first();

                                    // Simpan ke pegawai
                                    $pegawai = new Pegawai;
                                    $pegawai->status_kepeg_id = 1;
                                    $pegawai->status_kerja_id = 2;
                                    $pegawai->golongan_id = 0;
                                    $pegawai->golru_id = null;
                                    $pegawai->nip = $pegawai_non_aktif->nip;
                                    $pegawai->jenis = $pegawai_non_aktif->jenis;
                                    $pegawai->nama = $pegawai_non_aktif->nama;
                                    $pegawai->gelar_depan = $pegawai_non_aktif->gelar_depan;
                                    $pegawai->gelar_belakang = $pegawai_non_aktif->gelar_belakang;
                                    $pegawai->tmt_non_aktif = null;
                                    $pegawai->save();
                                }
                                
                                // Get anak satker
                                $a = AnakSatker::where('kode','=',$data[1])->first();

                                // Get gaji
                                $gaji = Gaji::where('jenis_id','=',$jenis->id)->where('kdanak','=',$data[1])->where('bulan','=',$data[3])->where('tahun','=',$data[4])->where('nip','=',$data[7])->first();
                                if(!$gaji) $gaji = new Gaji;

                                // Simpan gaji induk
                                $gaji->sk_id = $sk->id;
                                $gaji->pegawai_id = $pegawai ? $pegawai->id : 0;
                                $gaji->unit_id = $this->kdanak_to_unit($data[1]);
                                $gaji->anak_satker_id = $a->id;
                                $gaji->jenis_id = $jenis->id;
                                $gaji->jenis = $pegawai ? $pegawai->jenis : 0;
                                $gaji->kdanak = $data[1];
                                $gaji->bulan = $data[3];
                                $gaji->tahun = $data[4];
                                $gaji->nip = $data[7];
                                $gaji->nama = $data[8];
                                $gaji->gjpokok = $data[21];
                                $gaji->tjistri = $data[22];
                                $gaji->tjanak = $data[23];
                                $gaji->tjupns = $data[24];
                                $gaji->tjstruk = $data[25];
                                $gaji->tjfungs = $data[26];
                                $gaji->tjdaerah = $data[27];
                                $gaji->tjpencil = $data[28];
                                $gaji->tjlain = $data[29];
                                $gaji->tjkompen = $data[30];
                                $gaji->pembul = $data[31];
                                $gaji->tjberas = $data[32];
                                $gaji->tjpph = $data[33];
                                $gaji->potpfkbul = $data[34];
                                $gaji->potpfk2 = $data[35];
                                $gaji->potpfk10 = $data[36];
                                $gaji->potpph = $data[37];
                                $gaji->potswrum = $data[38];
                                $gaji->potkelbtj = $data[39];
                                $gaji->potlain = $data[40];
                                $gaji->pottabrum = $data[41];
                                $gaji->bpjs = $data[48];
                                $gaji->bpjs2 = $data[49];
                                $gaji->nominal = array_sum_range($data, 21, 33);
                                $gaji->potongan = array_sum_range($data, 34, 41) + $data[48] + $data[49];
                                $gaji->save();
                            }
                            // Import GPP Lama
                            else {
                                // Get jenis, anak satker, bulan, tahun
                                if($key == 0) {
                                    $jenis = JenisGaji::where('kode2','=',$data[7])->first();
                                    $a = AnakSatker::where('kode','=',$data[2])->first();
                                    $anak_satker = $a->nama;
                                    $bulan = DateTimeExt::month((int)$data[4]);
                                    $bulanAngka = (int)$data[4];
                                    $tahun = $data[5];
                                }

                                // Get pegawai
                                $pegawai = Pegawai::where('nip','=',$data[8])->first();

                                // Jika pegawai tidak ditemukan
                                if(!$pegawai) {
                                    // Get pegawai non aktif
                                    $pegawai_non_aktif = PegawaiNonAktif::where('nip','=',$data[8])->first();

                                    // Simpan ke pegawai
                                    $pegawai = new Pegawai;
                                    $pegawai->status_kepeg_id = 1;
                                    $pegawai->status_kerja_id = 2;
                                    $pegawai->golongan_id = 0;
                                    $pegawai->golru_id = null;
                                    $pegawai->nip = $pegawai_non_aktif->nip;
                                    $pegawai->jenis = $pegawai_non_aktif->jenis;
                                    $pegawai->nama = $pegawai_non_aktif->nama;
                                    $pegawai->gelar_depan = $pegawai_non_aktif->gelar_depan;
                                    $pegawai->gelar_belakang = $pegawai_non_aktif->gelar_belakang;
                                    $pegawai->tmt_non_aktif = null;
                                    $pegawai->save();
                                }

                                // Get anak satker
                                $a = AnakSatker::where('kode','=',$data[2])->first();

                                // Get gaji
                                $gaji = Gaji::where('jenis_id','=',$jenis->id)->where('kdanak','=',$data[2])->where('bulan','=',$data[4])->where('tahun','=',$data[5])->where('nip','=',$data[8])->first();
                                if(!$gaji) $gaji = new Gaji;

                                // Simpan gaji
                                $gaji->sk_id = $sk->id;
                                $gaji->pegawai_id = $pegawai ? $pegawai->id : 0;
                                $gaji->unit_id = $this->kdanak_to_unit($data[2]);
                                $gaji->anak_satker_id = $a->id;
                                $gaji->jenis_id = $jenis->id;
                                $gaji->jenis = $pegawai ? $pegawai->jenis : 0;
                                $gaji->kdanak = $data[2];
                                $gaji->bulan = $data[4];
                                $gaji->tahun = $data[5];
                                $gaji->nip = $data[8];
                                $gaji->nama = $data[9];
                                $gaji->gjpokok = $data[22];
                                $gaji->tjistri = $data[23];
                                $gaji->tjanak = $data[24];
                                $gaji->tjupns = $data[25];
                                $gaji->tjstruk = $data[26];
                                $gaji->tjfungs = $data[27];
                                $gaji->tjdaerah = $data[28];
                                $gaji->tjpencil = $data[29];
                                $gaji->tjlain = $data[30];
                                $gaji->tjkompen = $data[31];
                                $gaji->pembul = $data[32];
                                $gaji->tjberas = $data[33];
                                $gaji->tjpph = $data[34];
                                $gaji->potpfkbul = $data[35];
                                $gaji->potpfk2 = $data[36];
                                $gaji->potpfk10 = $data[37];
                                $gaji->potpph = $data[38];
                                $gaji->potswrum = $data[39];
                                $gaji->potkelbtj = $data[40];
                                $gaji->potlain = $data[41];
                                $gaji->pottabrum = $data[42];
                                $gaji->bpjs = array_key_exists(49, $data) ? $data[49] : 0;
                                $gaji->bpjs2 = array_key_exists(50, $data) ? $data[50] : 0;
                                $gaji->nominal = array_sum_range($data, 22, 34);
                                $gaji->potongan = array_sum_range($data, 35, 42) + $gaji->bpjs + $gaji->bpjs2;
                                $gaji->save();
                            }
                        }
                        // Kekurangan gaji
                        else {
                            // Get jenis, anak satker, bulan, tahun
                            if($key == 0) {
                                $jenis = JenisGaji::where('kode2','=',$data[6])->first();
                                $a = AnakSatker::where('kode','=',$data[1])->first();
                                $anak_satker = $a->nama;
                                $bulan = DateTimeExt::month((int)$data[3]);
                                $bulanAngka = (int)$data[3];
                                $tahun = $data[4];
                            }

                            // Get pegawai
                            $pegawai = Pegawai::where('nip','=',$data[7])->first();

                            // Jika pegawai tidak ditemukan
                            if(!$pegawai) {
                                // Get pegawai non aktif
                                $pegawai_non_aktif = PegawaiNonAktif::where('nip','=',$data[7])->first();

                                // Simpan ke pegawai
                                $pegawai = new Pegawai;
                                $pegawai->status_kepeg_id = 1;
                                $pegawai->status_kerja_id = 2;
                                $pegawai->golongan_id = 0;
                                $pegawai->golru_id = null;
                                $pegawai->nip = $pegawai_non_aktif->nip;
                                $pegawai->jenis = $pegawai_non_aktif->jenis;
                                $pegawai->nama = $pegawai_non_aktif->nama;
                                $pegawai->gelar_depan = $pegawai_non_aktif->gelar_depan;
                                $pegawai->gelar_belakang = $pegawai_non_aktif->gelar_belakang;
                                $pegawai->tmt_non_aktif = null;
                                $pegawai->save();
                            }
                            
                            // Get anak satker
                            $a = AnakSatker::where('kode','=',$data[1])->first();

                            // Get gaji
                            $gaji = Gaji::where('jenis_id','=',$jenis->id)->where('kdanak','=',$data[1])->where('bulan','=',$data[3])->where('tahun','=',$data[4])->where('nip','=',$data[7])->first();
                            if(!$gaji) $gaji = new Gaji;

                            // Simpan gaji induk
                            $gaji->sk_id = $sk->id;
                            $gaji->pegawai_id = $pegawai ? $pegawai->id : 0;
                            $gaji->unit_id = $this->kdanak_to_unit($data[1]);
                            $gaji->anak_satker_id = $a->id;
                            $gaji->jenis_id = $jenis->id;
                            $gaji->jenis = $pegawai ? $pegawai->jenis : 0;
                            $gaji->kdanak = $data[1];
                            $gaji->bulan = $data[3];
                            $gaji->tahun = $data[4];
                            $gaji->nip = $data[7];
                            $gaji->nama = $data[8];
                            $gaji->gjpokok = $data[23];
                            $gaji->tjistri = $data[24];
                            $gaji->tjanak = $data[25];
                            $gaji->tjupns = $data[26];
                            $gaji->tjstruk = $data[27];
                            $gaji->tjfungs = $data[28];
                            $gaji->tjdaerah = $data[29];
                            $gaji->tjpencil = $data[30];
                            $gaji->tjlain = $data[31];
                            $gaji->tjkompen = $data[32];
                            $gaji->pembul = $data[33];
                            $gaji->tjberas = $data[34];
                            $gaji->tjpph = $data[35];
                            $gaji->potpfkbul = $data[36];
                            $gaji->potpfk2 = $data[37];
                            $gaji->potpfk10 = $data[38];
                            $gaji->potpph = $data[39];
                            $gaji->potswrum = $data[40];
                            $gaji->potkelbtj = 0;
                            $gaji->potlain = 0;
                            $gaji->pottabrum = 0;
                            $gaji->bpjs = $data[42];
                            $gaji->bpjs2 = $data[43];
                            $gaji->nominal = array_sum_range($data, 23, 35);
                            $gaji->potongan = array_sum_range($data, 36, 40) + $data[42] + $data[43];
                            $gaji->save();
                        }
                    }
                }
            }

            // Rename the file
            File::move(public_path('storage/spreadsheets/gaji/'.$new), public_path('storage/spreadsheets/gaji/'.$jenis->nama.'_'.$anak_satker.'_'.$tahun.'_'.$bulan.'.'.$extension));

            // Delete the file
            File::delete(public_path('storage/spreadsheets/gaji/'.$new));

            // Redirect
            return redirect()->route('admin.gaji.monitoring', ['jenis' => $jenis->id, 'status' => $request->status, 'bulan' => $bulanAngka, 'tahun' => $tahun])->with(['message' => 'Berhasil memproses data.']);
        }
    }

    /**
     * Perubahan Gaji Induk
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function change(Request $request)
    {
		ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "-1");
		
        $bulan = $request->query('bulan') ?: date('n');
        $tahun = $request->query('tahun') ?: date('Y');
        $tanggal = $tahun.'-'.($bulan < 10 ? '0'.$bulan : $bulan).'-01';
        $status = $request->query('status') ?: 1;

        // Get gaji bulan ini
        $gaji_bulan_ini = Gaji::whereHas('anak_satker', function(Builder $query) use($status) {
            return $query->where('jenis','=',$status);
        })->where('jenis_id','=',1)->where('bulan','=',($bulan < 10 ? '0'.$bulan : $bulan))->where('tahun','=',$tahun)->get();

        // Set tanggal sebelumnya
        $tanggal_sebelum = date('Y-m-d', strtotime("-1 month", strtotime($tanggal)));

        // Get gaji bulan sebelumnya
        $gaji_bulan_sebelumnya = Gaji::whereHas('anak_satker', function(Builder $query) use($status) {
            return $query->where('jenis','=',$status);
        })->where('jenis_id','=',1)->where('bulan','=',date('m', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->pluck('pegawai_id')->toArray();

        // Pegawai masuk
        $cek_bulan_ini = [];
        if(count($gaji_bulan_ini) > 0) {
            foreach($gaji_bulan_ini->pluck('pegawai_id')->toArray() as $t) {
                if(!in_array($t, $gaji_bulan_sebelumnya))
                    array_push($cek_bulan_ini, $t);
            }
        }
		$pegawai_on = Pegawai::whereIn('id', $cek_bulan_ini)->get();

        // Pegawai keluar
        $cek_bulan_sebelumnya = [];
        if(count($gaji_bulan_sebelumnya) > 0) {
            foreach($gaji_bulan_sebelumnya as $t) {
                if(!in_array($t, $gaji_bulan_ini->pluck('pegawai_id')->toArray()))
                    array_push($cek_bulan_sebelumnya, $t);
            }
        }
		$pegawai_off = Pegawai::whereIn('id', $cek_bulan_sebelumnya)->get();
		
		// Perubahan gaji
		$perubahan_gjpokok = [];
		$perubahan_tjfungs = [];
		$perubahan_tjistri = [];
		$perubahan_tjanak = [];
		$perubahan_unit = [];
		foreach($gaji_bulan_ini as $g) {
			// Get gaji bulan sebelumnya
			$gs = Gaji::whereHas('anak_satker', function(Builder $query) use($status) {
                return $query->where('jenis','=',$status);
            })->where('jenis_id','=',1)->where('pegawai_id','=',$g->pegawai_id)->where('bulan','=',date('m', strtotime($tanggal_sebelum)))->where('tahun','=',date('Y', strtotime($tanggal_sebelum)))->first();
			if($gs) {
				if($g->gjpokok != $gs->gjpokok) array_push($perubahan_gjpokok, ['pegawai' => $g->pegawai, 'sebelum' => $gs->gjpokok, 'sesudah' => $g->gjpokok]);
				if($g->tjfungs != $gs->tjfungs) array_push($perubahan_tjfungs, ['pegawai' => $g->pegawai, 'sebelum' => $gs->tjfungs, 'sesudah' => $g->tjfungs]);
				if(($g->tjistri / (($g->gjpokok * 10) / 100)) != ($gs->tjistri / (($gs->gjpokok * 10) / 100))) array_push($perubahan_tjistri, ['pegawai' => $g->pegawai, 'sebelum' => ($gs->tjistri / (($gs->gjpokok * 10) / 100)), 'sesudah' => ($g->tjistri / (($g->gjpokok * 10) / 100))]);
				if(($g->tjanak / (($g->gjpokok * 2) / 100)) != ($gs->tjanak / (($gs->gjpokok * 2) / 100))) array_push($perubahan_tjanak, ['pegawai' => $g->pegawai, 'sebelum' => ($gs->tjanak / (($gs->gjpokok * 2) / 100)), 'sesudah' => ($g->tjanak / (($g->gjpokok * 2) / 100))]);
				if($g->unit_id != $gs->unit_id) array_push($perubahan_unit, ['pegawai' => $g->pegawai, 'sebelum' => $gs->unit, 'sesudah' => $g->unit]);
			}
		}
		
        // View
        return view('admin/gaji/change', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'status' => $status,
            'gaji_bulan_ini' => $gaji_bulan_ini,
            'gaji_bulan_sebelumnya' => $gaji_bulan_sebelumnya,
            'pegawai_on' => $pegawai_on,
            'pegawai_off' => $pegawai_off,
            'perubahan_gjpokok' => $perubahan_gjpokok,
            'perubahan_tjfungs' => $perubahan_tjfungs,
            'perubahan_tjistri' => $perubahan_tjistri,
            'perubahan_tjanak' => $perubahan_tjanak,
            'perubahan_unit' => $perubahan_unit,
        ]);
    }

    /**
     * Sync
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sync(Request $request)
    {
		ini_set("memory_limit", "-1");
        ini_set("max_execution_time", "-1");

        // Get SK
        $sk = SK::where('jenis_id','=',5)->where('status','=',1)->first();

        // Get gaji
        $gaji = Gaji::where('jenis_id','=',$request->query('jenis'))->get();
        
        foreach($gaji as $g) {
            // Get anak satker
            $anak_satker = AnakSatker::where('kode','=',$g->kdanak)->first();

            // Update
            $update = Gaji::find($g->id);
            $update->sk_id = $sk->id;
            $update->anak_satker_id = $anak_satker->id;
            $update->save();
        }
    }

    public function kdanak_to_unit($kdanak) {
        if($kdanak == "00") $anak = 6;
        elseif($kdanak == "01") $anak = 26;
        elseif($kdanak == "02") $anak = 10;
        elseif($kdanak == "03") $anak = 9;
        elseif($kdanak == "04") $anak = 7;
        elseif($kdanak == "05") $anak = 0;
        elseif($kdanak == "06") $anak = 11;
        elseif($kdanak == "07") $anak = 4;
        elseif($kdanak == "08") $anak = 4;
        elseif($kdanak == "09") $anak = 4;
        elseif($kdanak == "10") $anak = 1;
        elseif($kdanak == "11") $anak = 2;
        elseif($kdanak == "12") $anak = 12;
        else $anak = 0;

        return $anak;
    }
}
