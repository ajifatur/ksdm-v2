<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ajifatur\Helpers\DateTimeExt;
use App\Imports\MutasiImport;
use App\Models\Mutasi;
use App\Models\MutasiDetail;
use App\Models\Angkatan;
use App\Models\GajiPokok;
use App\Models\Golongan;
use App\Models\Golru;
use App\Models\Jabatan;
use App\Models\JenisMutasi;
use App\Models\LebihKurang;
use App\Models\Pegawai;
use App\Models\Perubahan;
use App\Models\Pejabat;
use App\Models\Referensi;
use App\Models\RemunGaji;
use App\Models\SK;
use App\Models\StatusKepegawaian;
use App\Models\TunjanganProfesi;
use App\Models\Unit;

class TempController extends Controller
{    
    /**
     * Import Mutasi Oktober 2023
     *
     * @return \Illuminate\Http\Response
     */
    public function importMutasiOktober2023(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Set default values
        $bulan = 10;
        $tahun = 2023;
        $uraian = "Perubahan Oktober 2023";

        // Get SK
        $sk = SK::where('jenis_id','=',1)->where('status','=',1)->first();

		$array = Excel::toArray(new MutasiImport, public_path('assets/spreadsheets/Perubahan_2023_Oktober_MutJab.xlsx'));

        // NB: Gaji pokok PPPK dikosongi dulu
        $error = [];
        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[0] != null) {
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',trim($data[1]))->first();
                    if(!$pegawai) {
                        // Get status kepegawaian
                        $status_kepegawaian = StatusKepegawaian::where('nama','=',$data[2])->first();

                        // Get golongan
                        $golongan = Golongan::where('nama','=',$data[4])->first();

                        // Set jenis / kategori
                        if($data[3] == 'DOSEN') $jenis = 1;
                        elseif($data[3] == 'TENDIK') $jenis = 2;
                        else $jenis = 0;

                        $pegawai = new Pegawai;
                        $pegawai->status_kepeg_id = $status_kepegawaian->id;
                        $pegawai->status_kerja_id = 1;
                        $pegawai->golongan_id = $golongan->id;
                        $pegawai->golru_id = null;
                        $pegawai->jabfung_id = null;
                        $pegawai->jabstruk_id = null;
                        $pegawai->unit_id = null;
                        $pegawai->nip = trim($data[1]);
                        $pegawai->jenis = $jenis;
                        $pegawai->nama = $data[0];
                        $pegawai->gelar_depan = '';
                        $pegawai->gelar_belakang = '';
                        $pegawai->tmt_cpns = null;
                        $pegawai->tmt_golongan = null;
                        $pegawai->tmt_non_aktif = null;
                        $pegawai->save();
                    }

                    if($data[9] == 'Mutasi Jabatan') {
                        // Get mutasi sebelum
                        $mutasi_sebelum = Mutasi::where('pegawai_id','=',$pegawai->id)->where('bulan','!=',0)->where('tahun','!=',0)->orderBy('tahun','desc')->orderBy('bulan','desc')->first();

                        // Get jabatan
                        $jabatan = Jabatan::where('sk_id','=',$sk->id)->where('nama','=',$data[6])->where('sub','=',$data[7])->first();
            
                        // Get unit
                        $unit = Unit::where('nama','=',$data[5])->first();
            
                        if($jabatan) {
                            // Get referensi
                            $referensi = Referensi::where('sk_id','=',$sk->id)->where('jabatan_dasar_id','=',$jabatan->jabatan_dasar_id)->where('layer_id','=',$unit->layer_id)->first();

                            // Get remun bulan sebelumnya
                            $remun_gaji = RemunGaji::where('pegawai_id','=',$pegawai->id)->where('bulan','=',$bulan-1)->where('tahun','=',$tahun)->first();

                            // Simpan mutasi
                            $mutasi = Mutasi::where('pegawai_id','=',$pegawai->id)->where('tmt','=',DateTimeExt::change($data[8]))->first();
                            if(!$mutasi) $mutasi = new Mutasi;
                            $mutasi->pegawai_id = $pegawai->id;
                            $mutasi->sk_id = $sk->id;
                            $mutasi->jenis_id = 1;
                            $mutasi->status_kepeg_id = $pegawai->status_kepeg_id;
                            $mutasi->golru_id = $remun_gaji ? $remun_gaji->golru_id : null;
                            $mutasi->gaji_pokok_id = $mutasi_sebelum ? $mutasi_sebelum->gaji_pokok_id : null;
                            $mutasi->bulan = 0;
                            $mutasi->tahun = 0;
                            $mutasi->uraian = $uraian;
                            $mutasi->tmt = DateTimeExt::change($data[8]);
                            $mutasi->remun_penerimaan = $remun_gaji ? mround(($remun_gaji->status_kepegawaian->persentase / 100) * $referensi->remun_standar, 1) : mround(($pegawai->status_kepegawaian->persentase / 100) * $referensi->remun_standar, 1);
                            $mutasi->remun_gaji = mround((30 / 100) * $mutasi->remun_penerimaan, 1);
                            $mutasi->remun_insentif = mround((70 / 100) * $mutasi->remun_penerimaan, 1);
                            $mutasi->save();

                            // Simpan mutasi detail
                            $mutasi_detail = MutasiDetail::where('mutasi_id','=',$mutasi->id)->where('jabatan_id','=',$jabatan->id)->first();
                            if(!$mutasi_detail) $mutasi_detail = new MutasiDetail;
                            $mutasi_detail->mutasi_id = $mutasi->id;
                            $mutasi_detail->jabatan_id = $jabatan->id;
                            $mutasi_detail->jabatan_dasar_id = $jabatan->jabatan_dasar_id;
                            $mutasi_detail->unit_id = $unit->id;
                            $mutasi_detail->layer_id = $unit->layer_id;
                            $mutasi_detail->status = 1;
                            $mutasi_detail->save();
					
							// Update jabfung_id dan unit_id pada pegawai
							if($jabatan->jenis_id == 1) {
								$pegawai->jabfung_id = $jabatan->grup_id;
								$pegawai->unit_id = $unit->id;
								$pegawai->save();
							}
					
                            // Update jabstruk_id pada pegawai
                            if($jabatan->jenis_id == 2) {
                                $pegawai->jabstruk_id = $jabatan->grup_id;
                                $pegawai->save();
                            }

                            // Jika jabatannya adalah struktural, maka otomatis menambahkan jabatan fungsional jika ada
                            if($jabatan->jenis_id == 2) {
                                // Get jabatan fungsional
                                $jabatan_fungsional = $mutasi_sebelum ? $mutasi_sebelum->detail()->whereHas('jabatan', function(Builder $query) {
                                    return $query->where('jenis_id','=',1);
                                })->first() : false;
                                if($jabatan_fungsional) {
                                    // Simpan mutasi detail
                                    $mutasi_detail_jf = MutasiDetail::where('mutasi_id','=',$mutasi->id)->where('jabatan_id','=',$jabatan_fungsional->jabatan->id)->first();
                                    if(!$mutasi_detail_jf) $mutasi_detail_jf = new MutasiDetail;
                                    $mutasi_detail_jf->mutasi_id = $mutasi->id;
                                    $mutasi_detail_jf->jabatan_id = $jabatan_fungsional->jabatan->id;
                                    $mutasi_detail_jf->jabatan_dasar_id = $jabatan_fungsional->jabatan->jabatan_dasar_id;
                                    $mutasi_detail_jf->unit_id = $jabatan_fungsional->unit->id;
                                    $mutasi_detail_jf->layer_id = $jabatan_fungsional->unit->layer_id;
                                    $mutasi_detail_jf->status = 0;
                                    $mutasi_detail_jf->save();
									
									// Update jabfung_id dan unit_id pada pegawai
									$pegawai->jabfung_id = $jabatan_fungsional->jabatan->grup_id;
									$pegawai->unit_id = $jabatan_fungsional->unit->id;
									$pegawai->save();
                                }
                            }
                        }
                        else array_push($error, $data[0]);
                    }
                    else {
                        // Get jenis mutasi
                        $jenis_mutasi = JenisMutasi::where('nama','=',$data[9])->first();
        
                        // Simpan mutasi
                        $mutasi = Mutasi::where('pegawai_id','=',$pegawai->id)->where('tmt','=',DateTimeExt::change($data[8]))->first();
                        if(!$mutasi) $mutasi = new Mutasi;
                        $mutasi->pegawai_id = $pegawai->id;
                        $mutasi->sk_id = $sk->id;
                        $mutasi->jenis_id = $jenis_mutasi->id;
                        $mutasi->status_kepeg_id = $pegawai->status_kepeg_id;
                        $mutasi->golru_id = null;
                        $mutasi->gaji_pokok_id = null;
                        $mutasi->bulan = 0;
                        $mutasi->tahun = 0;
                        $mutasi->uraian = $uraian;
                        $mutasi->tmt = DateTimeExt::change($data[8]);
                        $mutasi->remun_penerimaan = 0;
                        $mutasi->remun_gaji = 0;
                        $mutasi->remun_insentif = 0;
                        $mutasi->save();
                    }
                }
            }
        }

        // Redirect
        return redirect()->route('admin.mutasi.new')->with(['message' => 'Berhasil mengimport data. Error : '.(count($error) > 0 ? implode(', ', $error) : '-')]);
    }
    
    /**
     * Import Peralihan BLU ke Pegawai Tetap
     *
     * @return \Illuminate\Http\Response
     */
    public function importBLU(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        // Set default values
        $bulan = 10;
        $tahun = 2023;
        $uraian = "Peralihan BLU ke Pegawai Tetap PTNBH";

        // Get SK
        $sk = SK::where('jenis_id','=',1)->where('status','=',1)->first();

		$array = Excel::toArray(new MutasiImport, public_path('storage/BLU 2023.xlsx'));

        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[0] != null) {
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',trim($data[1]))->first();
    
                    // Get gaji pokok
                    $gaji_pokok = GajiPokok::where('nama','=',$data[7])->first();
    
                    // Simpan mutasi
                    $mutasi = Mutasi::where('jenis_id','=',13)->where('pegawai_id','=',$pegawai->id)->first();
                    if(!$mutasi) $mutasi = new Mutasi;
                    $mutasi->pegawai_id = $pegawai->id;
                    $mutasi->sk_id = $sk->id;
                    $mutasi->jenis_id = 13;
                    $mutasi->status_kepeg_id = 3;
                    $mutasi->golru_id = $gaji_pokok->golru_id;
                    $mutasi->gaji_pokok_id = $gaji_pokok->id;
                    $mutasi->bulan = 10;
                    $mutasi->tahun = 2023;
                    $mutasi->uraian = $uraian;
                    $mutasi->tmt = '2023-10-01';
                    $mutasi->remun_penerimaan = 0;
                    $mutasi->remun_gaji = 0;
                    $mutasi->remun_insentif = 0;
                    $mutasi->save();
    
                    // Simpan pegawai
                    $pegawai->golongan_id = $gaji_pokok->golru->golongan_id;
                    $pegawai->golru_id = $gaji_pokok->golru_id;
                    $pegawai->npu = $data[2];
                    $pegawai->tmt_golongan = DateTimeExt::change($data[8]);
                    $pegawai->save();
    
                    // Get mutasi pegawai
                    $m = $pegawai->mutasi()->where('jenis_id','=',1)->where('tmt','<=',$mutasi->tmt)->first();
                    if($m) {
                        foreach($m->detail as $d) {
                            // Simpan mutasi detail
                            $detail = new MutasiDetail;
                            $detail->mutasi_id = $mutasi->id;
                            $detail->jabatan_id = $d->jabatan_id;
                            $detail->jabatan_dasar_id = $d->jabatan_dasar_id;
                            $detail->unit_id = $d->unit_id;
                            $detail->layer_id = $d->layer_id;
                            $detail->status = $d->status;
                            $detail->save();
                        }
                    }

                    // Simpan perubahan
                    $perubahan = $mutasi->perubahan;
                    if(!$perubahan) $perubahan = new Perubahan;
                    $perubahan->mutasi_id = $mutasi->id;
                    $perubahan->sk_id = $gaji_pokok->sk_id;
                    $perubahan->pejabat_id = 5;
                    $perubahan->no_sk = $data[9];
                    $perubahan->tanggal_sk = '2023-10-06';
                    $perubahan->mk_tahun = $data[4];
                    $perubahan->mk_bulan = $data[5];
                    $perubahan->tmt = '2023-10-01';
                    $perubahan->save();
                }
            }
        }
    }
    
    /**
     * Import from Excel
     *
     * @return \Illuminate\Http\Response
     */
    public function importSerdosJanuari2024(Request $request)
    {
		ini_set("memory_limit", "-1");
		ini_set("max_execution_time", "-1");

        $bulan = 1;
        $tahun = 2024;
        $array = Excel::toArray(new TunjanganProfesiImport, public_path('storage/Serdos 2024.xlsx'));

        $error = [];
        if(count($array)>0) {
            foreach($array[0] as $data) {
                if($data[0] != null) {
                    // Get pegawai
                    $pegawai = Pegawai::where('nip','=',$data[0])->first();
                    if(!$pegawai) array_push($error, $data[1]);

                    // Get jenis
                    $jenis = $data[10];

                    // Get SK
                    $sk = SK::where('jenis_id','=',$data[9]+1)->where('awal_tahun','=',$tahun)->first();

                    // Get angkatan
                    if($jenis == 1 || $jenis == 2) {
                        if(in_array($data[8], [2014,2015]))
                            $angkatan = Angkatan::where('jenis_id','=',$jenis)->where('nama','=','2014-2015')->first();
                        else
                            $angkatan = Angkatan::where('jenis_id','=',$jenis)->where('nama','=',$data[8])->first();
                    }
                    elseif($jenis == 3 || $jenis == 4) {
                        if(in_array($data[8], [2020,2021,2022,2023]))
                            $angkatan = Angkatan::where('jenis_id','=',$jenis)->where('nama','=','2020-2023')->first();
                        else
                            $angkatan = Angkatan::where('jenis_id','=',$jenis)->where('nama','=',$data[8])->first();
                    }

                    // Get tunjangan profesi yang sudah ada
                    $tp = $pegawai->tunjangan_profesi()->whereHas('angkatan', function(Builder $query) use ($jenis) {
                        return $query->where('jenis_id','=',$jenis);
                    })->first();

                    // Simpan tunjangan profesi
                    $tunjangan = TunjanganProfesi::whereHas('angkatan', function(Builder $query) use ($jenis) {
                        return $query->where('jenis_id','=',$jenis);
                    })->where('pegawai_id','=',$pegawai->id)->where('sk_id','=',$sk->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();
                    if(!$tunjangan) $tunjangan = new TunjanganProfesi;
                    $tunjangan->pegawai_id = $pegawai->id;
                    $tunjangan->sk_id = $sk->id;
                    $tunjangan->angkatan_id = $angkatan->id;
                    $tunjangan->unit_id = $pegawai->unit_id;
                    $tunjangan->golongan_id = substr($data[3],0,1);
                    $tunjangan->nip = $pegawai->nip;
                    $tunjangan->nama = $tp ? $tp->nama : '';
                    $tunjangan->nomor_rekening = $tp ? $tp->nomor_rekening : '';
                    $tunjangan->nama_rekening = $tp ? $tp->nama_rekening : '';
                    $tunjangan->bulan = $bulan;
                    $tunjangan->tahun = $tahun;
                    $tunjangan->tunjangan = ($jenis == 1) ? 2 * $data[7] : $data[7];
                    $tunjangan->pph = ($tunjangan->golongan_id == 4) ? (15 / 100) * $tunjangan->tunjangan : (5 / 100) * $tunjangan->tunjangan;
                    $tunjangan->diterimakan = $tunjangan->tunjangan - $tunjangan->pph;
                    $tunjangan->kekurangan = 0;
                    $tunjangan->bulan_kurang = 0;
                    $tunjangan->tahun_kurang = 0;
                    $tunjangan->save();

                    // Update pegawai
                    $pegawai->nama_supplier = $tunjangan->nama;
                    $pegawai->nama_btn = $tunjangan->nama_rekening;
                    $pegawai->norek_btn = $tunjangan->nomor_rekening;
                    $pegawai->save();

                    /*
                    $tp = $pegawai->tunjangan_profesi()->where('tahun','!=',2024)->first();
                    $tunjangan = TunjanganProfesi::where('pegawai_id','=',$pegawai->id)->where('sk_id','=',$sk->id)->where('bulan','=',$bulan)->where('tahun','=',$tahun)->first();
                    if($tunjangan->nama == '') {
                        $tunjangan->nama = $tp ? $tp->nama : '';
                        $tunjangan->nomor_rekening = $tp ? $tp->nomor_rekening : '';
                        $tunjangan->nama_rekening = $tp ? $tp->nama_rekening : '';
                        $tunjangan->save();

                        $pegawai->nama_supplier = $tunjangan->nama;
                        $pegawai->nama_btn = $tunjangan->nama_rekening;
                        $pegawai->norek_btn = $tunjangan->nomor_rekening;
                        $pegawai->save();
                    }
                    */
                }
            }
        }
        var_dump($error);
        return;
    }

    /**
     * Import (PPPK)
     *
     * @return \Illuminate\Http\Response
     */
    public function importUangMakanPPPK(Request $request)
    {
        if($request->method() == 'GET') {
            // Get anak satker
            $anak_satker = AnakSatker::where('jenis','=',2)->get();

            // View
            return view('admin/uang-makan/import', [
                'anak_satker' => $anak_satker,
                'jenis' => 'PPPK',
                'route' => 'admin.uang-makan.import.pppk',
            ]);
        }
        elseif($request->method() == 'POST') {
            ini_set("memory_limit", "-1");
            ini_set("max_execution_time", "-1");
            
            // Make directory if not exists
            if(!File::exists(public_path('storage/spreadsheets/um')))
                File::makeDirectory(public_path('storage/spreadsheets/um'));

            // Get the file
            $file = $request->file('file');
            $filename = FileExt::info($file->getClientOriginalName())['nameWithoutExtension'];
            $extension = FileExt::info($file->getClientOriginalName())['extension'];
            $new = date('Y-m-d-H-i-s').'_'.$filename.'.'.$extension;

            // Move the file
            $file->move(public_path('storage/spreadsheets/um'), $new);

            // Get array
            $array = Excel::toArray(new UangMakanImport, public_path('storage/spreadsheets/um/'.$new));

            $anak_satker = '';
            $bulan = '';
            $bulanAngka = '';
            $tahun = '';
            if(count($array)>0) {
                foreach($array[0] as $key=>$data) {
                    if($data[1] != null) {
                        // Get pegawai
                        $pegawai = Pegawai::where('nip','=',$data[24])->first();

                        // Get tarif dan PPH
                        if(in_array($pegawai->golongan->nama, ['XIII','XIV','XV','XVI','XVII'])) {
                            $tarif = 41000;
                            $pph = 15;
                        }
                        elseif(in_array($pegawai->golongan->nama, ['IX','X','XI','XII'])) {
                            $tarif = 37000;
                            $pph = 5;
                        }
                        else {
                            $tarif = 35000;
                            $pph = 0;
                        }

                        // Get anak satker
                        $as = AnakSatker::where('kode','=',$request->anak_satker)->first();

                        // Get uang makan
                        $uang_makan = UangMakan::where('kdanak','=',$request->anak_satker)->where('bulan','=',$request->bulan)->where('tahun','=',$request->tahun)->where('nip','=',$data[24])->first();
                        if(!$uang_makan) $uang_makan = new UangMakan;

                        // Simpan uang makan
                        $uang_makan->pegawai_id = $pegawai ? $pegawai->id : 0;
                        $uang_makan->unit_id = $pegawai->unit_id;
                        $uang_makan->anak_satker_id = $as->id;
                        $uang_makan->jenis = $pegawai ? $pegawai->jenis : 0;
                        $uang_makan->kdanak = $request->anak_satker;
                        $uang_makan->bulan = $request->bulan;
                        $uang_makan->tahun = $request->tahun;
                        $uang_makan->nip = $data[24];
                        $uang_makan->nama = $data[1];
                        $uang_makan->jmlhari = (100 * $data[6]) / ((100 - $pph) * $tarif);
                        $uang_makan->tarif = $tarif;
                        $uang_makan->pph = $pph;
                        $uang_makan->kotor = $uang_makan->jmlhari * $tarif;
                        $uang_makan->potongan = ($pph / 100) * $uang_makan->kotor;
                        $uang_makan->bersih = $data[6];
                        $uang_makan->save();

                        // Get anak satker, bulan, tahun
                        if($key == 0) {
                            $a = AnakSatker::where('kode','=',$request->anak_satker)->first();
                            $anak_satker = $a->nama;
                            $bulan = DateTimeExt::month($request->bulan);
                            $bulanAngka = $request->bulan;
                            $tahun = $request->tahun;
                        }
                    }
                }
            }

            // Rename the file
            File::move(public_path('storage/spreadsheets/um/'.$new), public_path('storage/spreadsheets/um/'.$anak_satker.'_'.$tahun.'_'.$bulan.'.'.$extension));

            // Delete the file
            File::delete(public_path('storage/spreadsheets/um/'.$new));

            // Redirect
            return redirect()->route('admin.uang-makan.monitoring', ['bulan' => $bulanAngka, 'tahun' => $tahun, 'jenis' => 2])->with(['message' => 'Berhasil memproses data.']);
        }
    }
}
