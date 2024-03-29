<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Pegawai;
use App\Models\RemunGaji;
use App\Models\RemunInsentif;
use App\Models\LebihKurang;
use App\Models\TunjanganProfesi;
use App\Models\Gaji;
use App\Models\GajiNonASN;
use App\Models\UangMakan;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Check the access
        // has_access(__METHOD__, Auth::user()->role_id);
        
        // Count Dosen
        $dosen = Pegawai::where('jenis','=',1)->whereHas('status_kerja', function(Builder $query) {
            return $query->where('status','=',1);
        })->count();

        // Count Tendik
        $tendik = Pegawai::where('jenis','=',2)->whereHas('status_kerja', function(Builder $query) {
            return $query->where('status','=',1);
        })->count();

        // Sum remun gaji
        $remun_gaji_total = RemunGaji::where('tahun','=',date('Y'))->sum('remun_gaji') + LebihKurang::where('tahun_proses','=',date('Y'))->where('triwulan_proses','=',0)->where('kekurangan','=',0)->sum('selisih');
        $remun_gaji = RemunGaji::where('bulan','=',date('n'))->where('tahun','=',date('Y'))->sum('remun_gaji') + LebihKurang::where('bulan_proses','=',date('n'))->where('tahun_proses','=',date('Y'))->where('triwulan_proses','=',0)->where('kekurangan','=',0)->sum('selisih');

        // Sum remun insentif
        $remun_insentif_terakhir = RemunInsentif::latest('tahun')->latest('triwulan')->whereIn('triwulan',[1,2,3,4])->first();
        $remun_insentif_total = RemunInsentif::where('tahun','=',date('Y'))->whereIn('triwulan',[1,2,3,4])->sum('remun_insentif') + LebihKurang::where('tahun_proses','=',date('Y'))->where('triwulan_proses','!=',0)->sum('selisih');
        $remun_insentif = RemunInsentif::where('triwulan','=',$remun_insentif_terakhir->triwulan)->whereIn('triwulan',[1,2,3,4])->where('tahun','=',$remun_insentif_terakhir->tahun)->sum('remun_insentif') + LebihKurang::where('triwulan_proses','=',$remun_insentif_terakhir->triwulan)->where('tahun_proses','=',$remun_insentif_terakhir->tahun)->sum('selisih');

        // Sum gaji induk ASN
        $gaji_induk_total = Gaji::where('tahun','=',date('Y'))->sum('nominal') - Gaji::where('tahun','=',date('Y'))->sum('potongan');
        $gaji_induk = Gaji::where('bulan','=',(date('n') < 10 ? '0'.date('n') : date('n')))->where('tahun','=',date('Y'))->sum('nominal') - Gaji::where('bulan','=',(date('n') < 10 ? '0'.date('n') : date('n')))->where('tahun','=',date('Y'))->sum('potongan');

        // Sum gaji induk Non ASN
        $gaji_non_asn_total = GajiNonASN::where('tahun','=',date('Y'))->sum('nominal');
        $gaji_non_asn = GajiNonASN::where('bulan','=',date('n'))->where('tahun','=',date('Y'))->sum('nominal');

        // Sum uang makan
        $uang_makan_terakhir = UangMakan::latest('tahun')->latest('bulan')->first();
        $uang_makan_total = UangMakan::where('tahun','=',date('Y'))->sum('bersih');
        $uang_makan = UangMakan::where('bulan','=',$uang_makan_terakhir->bulan)->where('tahun','=',$uang_makan_terakhir->tahun)->sum('bersih');

        // Sum tunjangan kehormatan profesor
        $tunjangan_profesor_total = TunjanganProfesi::whereHas('angkatan', function (Builder $query) {
            return $query->where('jenis_id','=',1);
        })->where('tahun','=',date('Y'))->sum('diterimakan');
        $tunjangan_profesor = TunjanganProfesi::whereHas('angkatan', function (Builder $query) {
            return $query->where('jenis_id','=',1);
        })->where('bulan','=',date('n'))->where('tahun','=',date('Y'))->sum('diterimakan');

        // Sum tunjangan profesi
        $tunjangan_profesi_total = TunjanganProfesi::whereHas('angkatan', function (Builder $query) {
            return $query->whereIn('jenis_id',[2,3,4]);
        })->where('tahun','=',date('Y'))->sum('diterimakan');
        $tunjangan_profesi = TunjanganProfesi::whereHas('angkatan', function (Builder $query) {
            return $query->whereIn('jenis_id',[2,3,4]);
        })->where('bulan','=',date('n'))->where('tahun','=',date('Y'))->sum('diterimakan');

        // View
        return view('admin/dashboard', [
            'dosen' => $dosen,
            'tendik' => $tendik,
            'remun_gaji_total' => $remun_gaji_total,
            'remun_gaji' => $remun_gaji,
            'remun_insentif_terakhir' => $remun_insentif_terakhir,
            'remun_insentif_total' => $remun_insentif_total,
            'remun_insentif' => $remun_insentif,
            'gaji_induk_total' => $gaji_induk_total,
            'gaji_induk' => $gaji_induk,
            'gaji_non_asn_total' => $gaji_non_asn_total,
            'gaji_non_asn' => $gaji_non_asn,
            'uang_makan_terakhir' => $uang_makan_terakhir,
            'uang_makan_total' => $uang_makan_total,
            'uang_makan' => $uang_makan,
            'tunjangan_profesor_total' => $tunjangan_profesor_total,
            'tunjangan_profesor' => $tunjangan_profesor,
            'tunjangan_profesi_total' => $tunjangan_profesi_total,
            'tunjangan_profesi' => $tunjangan_profesi,
        ]);
    }
}
