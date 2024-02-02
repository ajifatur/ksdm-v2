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
}
