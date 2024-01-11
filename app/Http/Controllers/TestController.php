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

        $title = 'Tes';

        $grand_total['terbayar'] = 0;
        $grand_total['seharusnya'] = 0;
        $grand_total['selisih'] = 0;

        // Get array
        $error = [];
        $j = [];
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
                        $gaji = GajiNonASN::where('pegawai_id','=',$pegawai->id)->where('bulan','>=',8)->where('tahun','=',2023)->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
                        $keterangan = 'Kekurangan KGB Agustus s.d. Desember 2023';
                        $tmt = '01-08-2023';
                    }
                    elseif($data[4] == 'Rapel KGB Agustus-Desember 4') {
                        $gaji = GajiNonASN::where('pegawai_id','=',$pegawai->id)->where('bulan','>=',1)->where('tahun','=',2023)->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
                        $keterangan = 'Kekurangan KGB Januari s.d. Desember 2023';
                        $tmt = '01-08-2021';
                    }
                    elseif($data[4] == 'Rapel KP April-Mei') {
                        $gaji = GajiNonASN::where('pegawai_id','=',$pegawai->id)->where('bulan','>=',4)->where('bulan','<=',5)->where('tahun','=',2023)->orderBy('tahun','desc')->orderBy('bulan','desc')->get();
                        $keterangan = 'Rapel KP April s.d. Mei 2023';
                        $tmt = '01-04-2023';
                    }

                    $total_terbayar = 0;
                    $total_seharusnya = 0;
                    $total_selisih = 0;

                    if(in_array($data[4], ['Rapel KGB Agustus-Desember 2','Rapel KGB Agustus-Desember 4','Rapel KP April-Mei'])) {
                        foreach($gaji as $key=>$g) {
                            $gaji[$key]->pasangan = ($g->tjistri == (10/100) * $g->gjpokok) ? 1 : 0;
                            $gaji[$key]->anak = ($g->tjanak > 0) ? ($g->tjanak / ((2/100) * $g->gjpokok)) : 0;
                            $gaji[$key]->status_kawin = '1'.$gaji[$key]->pasangan.'0'.$gaji[$key]->anak;
                            $gaji[$key]->tjistri_seharusnya = ($gaji[$key]->pasangan > 0) ? (10/100) * $mutasi->gaji_pokok->gaji_pokok : 0;
                            $gaji[$key]->tjanak_seharusnya = ($gaji[$key]->anak > 0) ? (2/100) * $mutasi->gaji_pokok->gaji_pokok * $gaji[$key]->anak : 0;
                            $gaji[$key]->gjpokok_selisih = $mutasi->gaji_pokok->gaji_pokok - $g->gjpokok;
                            $gaji[$key]->tjistri_selisih = $gaji[$key]->tjistri_seharusnya - $g->tjistri;
                            $gaji[$key]->tjanak_selisih = $gaji[$key]->tjanak_seharusnya - $g->tjanak;

                            $total_terbayar += ($g->gjpokok + $g->tjistri + $g->tjanak);
                            $total_seharusnya += ($mutasi->gaji_pokok->gaji_pokok + $gaji[$key]->tjistri_seharusnya + $gaji[$key]->tjanak_seharusnya);
                            $total_selisih += ($gaji[$key]->gjpokok_selisih + $gaji[$key]->tjistri_selisih + $gaji[$key]->tjanak_selisih);

                            if($key == 0) $status_kawin = $gaji[$key]->status_kawin;
                        }

                        $grand_total['terbayar'] += $total_terbayar;
                        $grand_total['seharusnya'] += $total_seharusnya;
                        $grand_total['selisih'] += $total_selisih;
                    
                        // Push
                        array_push($j, [
                            'pegawai' => $pegawai,
                            'mutasi' => $mutasi,
                            'gaji' => $gaji,
                            'total_terbayar' => $total_terbayar,
                            'total_seharusnya' => $total_seharusnya,
                            'total_selisih' => $total_selisih,
                            'status_kawin' => $status_kawin,
                            'keterangan' => $keterangan,
                            'tmt' => $tmt,
                        ]);
                    }
                }
            }
        }

        // PDF
        $pdf = PDF::loadView('admin/test/print', [
            'title' => $title,
            'j' => $j,
            'grand_total' => $grand_total,
        ]);
        $pdf->setPaper([0, 0 , 935, 612]);
        return $pdf->stream($title.'.pdf');
    }
}
