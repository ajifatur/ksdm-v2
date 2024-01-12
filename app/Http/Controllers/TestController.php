<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use PDF;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ajifatur\Helpers\DateTimeExt;
use App\Exports\TestExport;
use App\Imports\UangMakanImport;
use App\Models\Pegawai;
use App\Models\GajiNonASN;
use App\Models\GajiPokok;

class TestController extends Controller
{
    /**
     * Export.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Get pegawai
        $pegawai = Pegawai::where('status_kepeg_id','=',1)->where('status_kerja_id','=',1)->limit(10)->get();

        // Return
        return Excel::download(new TestExport($pegawai), 'Test.xlsx');
    }

    /**
     * Import.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        $title = 'DAFTAR PERHITUNGAN PEMBAYARAN KEKURANGAN GAJI PEGAWAI TETAP NON ASN UNIVERSITAS NEGERI SEMARANG TAHUN 2023';

        $grand_total['terbayar'] = 0;
        $grand_total['seharusnya'] = 0;
        $grand_total['selisih'] = 0;

        // Get array
        $error = [];
        $d = [];
        $j = [];
        $t_terbayar = [];
        $t_seharusnya = [];
        $t_selisih = [];
        $array = Excel::toArray(new UangMakanImport, public_path('storage/Hitung Rapel Gaji Pegawai BLU 2023 Rev.xlsx'));
        if(count($array)>0) {
            foreach($array[0] as $key=>$data) {
                if($data[0] != null) {
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',$data[0])->orWhere('npu','=',$data[0])->first();
                    if(!$pegawai) array_push($error, $data[1]);

                    // Get mutasi peralihan
                    $mutasi = $pegawai->mutasi()->where('jenis_id','=',13)->first();

                    if($data[4] == 'Rapel KGB Agustus-Desember 2') {
                        $gaji = GajiNonASN::where('pegawai_id','=',$pegawai->id)->where('bulan','>=',8)->where('bulan','<=',12)->where('tahun','=',2023)->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
                        $keterangan = 'Kekurangan KGB Agustus s.d. Desember 2023';
                        $tmt = '01-08-2023';
                    }
                    elseif($data[4] == 'Rapel KGB Januari-Juli') {
                        $gaji = GajiNonASN::where('pegawai_id','=',$pegawai->id)->where('bulan','>=',1)->where('bulan','<=',7)->where('tahun','=',2023)->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
                        $keterangan = 'Kekurangan KGB Januari s.d. Juli 2023';
                        $tmt = '01-08-2021';
                    }
                    elseif($data[4] == 'Rapel KGB Agustus-September' || $data[4] == 'Rapel KGB Agustus-September 2') {
                        $gaji = GajiNonASN::where('pegawai_id','=',$pegawai->id)->where('bulan','>=',8)->where('bulan','<=',9)->where('tahun','=',2023)->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
                        $keterangan = 'Kekurangan KGB Agustus s.d. September 2023';
                        $tmt = '01-08-2023';
                    }
                    elseif($data[4] == 'Rapel KP April-Mei') {
                        $gaji = GajiNonASN::where('pegawai_id','=',$pegawai->id)->where('bulan','>=',4)->where('bulan','<=',5)->where('tahun','=',2023)->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
                        $keterangan = 'Kekurangan Kenaikan Pangkat April s.d. Mei 2023';
                        $tmt = '01-04-2023';
                    }
                    elseif($data[4] == 'Rapel Peralihan Oktober-Desember') {
                        $gaji = GajiNonASN::where('pegawai_id','=',$pegawai->id)->where('bulan','>=',10)->where('bulan','<=',12)->where('tahun','=',2023)->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
                        $keterangan = 'Kekurangan Peralihan BLU ke PTNBH Oktober s.d. Desember 2023';
                        $tmt = '01-10-2023';
                    }
                    elseif($data[4] == 'Rapel KP Oktober-Desember') {
                        $gaji = GajiNonASN::where('pegawai_id','=',$pegawai->id)->where('bulan','>=',10)->where('bulan','<=',12)->where('tahun','=',2023)->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
                        $keterangan = 'Kekurangan Kenaikan Pangkat Oktober s.d. Desember 2023';
                        $tmt = '01-10-2023';
                    }

                    $total_terbayar = 0;
                    $total_seharusnya = 0;
                    $total_selisih = 0;

                    if(in_array($data[4], ['Rapel KGB Agustus-Desember 2','Rapel KGB Januari-Juli','Rapel KGB Agustus-September','Rapel KGB Agustus-September 2','Rapel KP April-Mei','Rapel Peralihan Oktober-Desember','Rapel KP Oktober-Desember'])) {
                        $status = [];
                        $rincian = [];
                        foreach($gaji as $key=>$g) {
                            if($data[4] == 'Rapel KGB Januari-Juli') {
                                $gaji_pokok = GajiPokok::where('gaji_pokok','=',$g->gjpokok)->first();
                                $tambahan_mk = substr($gaji_pokok->nama,0,2).((int)substr($gaji_pokok->nama,2,2) + 2) < 10 ? substr($gaji_pokok->nama,0,2).'0'.((int)substr($gaji_pokok->nama,2,2) + 2) : substr($gaji_pokok->nama,0,2).((int)substr($gaji_pokok->nama,2,2) + 2);
                                $gaji_pokok_baru = GajiPokok::where('nama','=',$tambahan_mk)->first();
                                $gaji[$key]->gjpokok_seharusnya = $gaji_pokok_baru ? $gaji_pokok_baru->gaji_pokok : 0;
                            }
                            elseif($data[4] == 'Rapel KGB Agustus-September') {
                                $gaji_pokok = GajiPokok::where('gaji_pokok','=',$g->gjpokok)->first();
                                $tambahan_mk = substr($gaji_pokok->nama,0,2).((int)substr($gaji_pokok->nama,2,2) + 4) < 10 ? substr($gaji_pokok->nama,0,2).'0'.((int)substr($gaji_pokok->nama,2,2) + 4) : substr($gaji_pokok->nama,0,2).((int)substr($gaji_pokok->nama,2,2) + 4);
                                $gaji_pokok_baru = GajiPokok::where('nama','=',$tambahan_mk)->first();
                                $gaji[$key]->gjpokok_seharusnya = $gaji_pokok_baru ? $gaji_pokok_baru->gaji_pokok : 0;
                            }
                            elseif($data[4] == 'Rapel KP Oktober-Desember') {
                                $gaji_pokok = GajiPokok::where('gaji_pokok','=',$mutasi->gaji_pokok->gaji_pokok)->first();
                                if(substr($gaji_pokok->nama,1,1) == 'A') $golru_baru = 'B';
                                elseif(substr($gaji_pokok->nama,1,1) == 'B') $golru_baru = 'C';
                                elseif(substr($gaji_pokok->nama,1,1) == 'C') $golru_baru = 'D';
                                $gaji_pokok_baru = GajiPokok::where('nama','=',substr($gaji_pokok->nama,0,1).$golru_baru.substr($gaji_pokok->nama,2,2))->first();
                                $gaji[$key]->gjpokok_seharusnya = $gaji_pokok_baru ? $gaji_pokok_baru->gaji_pokok : 0;
                            }
                            else {
                                $gaji[$key]->gjpokok_seharusnya = $mutasi->gaji_pokok->gaji_pokok;
                            }

                            $gaji[$key]->pasangan = ($g->tjistri == (10/100) * $g->gjpokok) ? 1 : 0;
                            $gaji[$key]->anak = ($g->tjanak > 0) ? ($g->tjanak / ((2/100) * $g->gjpokok)) : 0;
                            $gaji[$key]->status_kawin = '1'.$gaji[$key]->pasangan.'0'.$gaji[$key]->anak;
                            $gaji[$key]->tjistri_seharusnya = ($gaji[$key]->pasangan > 0) ? (10/100) * $gaji[$key]->gjpokok_seharusnya : 0;
                            $gaji[$key]->tjanak_seharusnya = ($gaji[$key]->anak > 0) ? (2/100) * $gaji[$key]->gjpokok_seharusnya * $gaji[$key]->anak : 0;
                            $gaji[$key]->gjpokok_selisih = $gaji[$key]->gjpokok_seharusnya - $g->gjpokok;
                            $gaji[$key]->tjistri_selisih = $gaji[$key]->tjistri_seharusnya - $g->tjistri;
                            $gaji[$key]->tjanak_selisih = $gaji[$key]->tjanak_seharusnya - $g->tjanak;
                            
                            $total_terbayar += ($g->gjpokok + $g->tjistri + $g->tjanak);
                            $total_seharusnya += ($gaji[$key]->gjpokok_seharusnya + $gaji[$key]->tjistri_seharusnya + $gaji[$key]->tjanak_seharusnya);
                            $total_selisih += ($gaji[$key]->gjpokok_selisih + $gaji[$key]->tjistri_selisih + $gaji[$key]->tjanak_selisih);

                            if($key == 0) $status_kawin = $gaji[$key]->status_kawin;

                            if(!array_key_exists($gaji[$key]->status_kawin, $rincian)) $rincian[$gaji[$key]->status_kawin] = [];
                            array_push($rincian[$gaji[$key]->status_kawin], $gaji[$key]);
                        }

                        $grand_total['terbayar'] += $total_terbayar;
                        $grand_total['seharusnya'] += $total_seharusnya;
                        $grand_total['selisih'] += $total_selisih;

                        if(!array_key_exists($pegawai->npu, $d)) {
                            $d[$pegawai->npu] = [];
                            $t_terbayar[$pegawai->npu] = [];
                            $t_seharusnya[$pegawai->npu] = [];
                            $t_selisih[$pegawai->npu] = [];
                        }
                        array_push($d[$pegawai->npu], [
                            'pegawai' => $pegawai,
                            'mutasi' => $mutasi,
                            'gaji' => $gaji,
                            'total_terbayar' => $total_terbayar,
                            'total_seharusnya' => $total_seharusnya,
                            'total_selisih' => $total_selisih,
                            'status_kawin' => $status_kawin,
                            'keterangan' => $keterangan,
                            'tmt' => $tmt,
                            'rincian' => $rincian,
                        ]);
                    }
                }
            }
        }

        foreach($d as $npu=>$j) {
            foreach($j as $data) {
                foreach($data['rincian'] as $rincian) {
                    $t1 = 0;
                    $t2 = 0;
                    $t3 = 0;
                    foreach($rincian as $r) {
                        $t1 += ($r->gjpokok + $r->tjistri + $r->tjanak);
                        $t2 += ($r->gjpokok_seharusnya + $r->tjistri_seharusnya + $r->tjanak_seharusnya);
                        $t3 += ($r->gjpokok_selisih + $r->tjistri_selisih + $r->tjanak_selisih);
                    }
                    array_push($t_terbayar[$npu], $t1);
                    array_push($t_seharusnya[$npu], $t2);
                    array_push($t_selisih[$npu], $t3);
                }
            }
        }  

        // PDF
        $pdf = PDF::loadView('admin/test/print-2', [
            'title' => $title,
            'd' => $d,
            't_terbayar' => $t_terbayar,
            't_seharusnya' => $t_seharusnya,
            't_selisih' => $t_selisih,
            'grand_total' => $grand_total,
        ]);
        $pdf->setPaper([0, 0 , 935, 612]);
        return $pdf->stream($title.'.pdf');

        // // Return
        // return Excel::download(new TestExport([
        //     'd' => $d,
        //     't_terbayar' => $t_terbayar,
        //     't_seharusnya' => $t_seharusnya,
        //     't_selisih' => $t_selisih
        // ]), 'Test.xlsx');
    }
}
