<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_pegawai';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];
    
    /**
     * Status Kepegawaian.
     */
    public function status_kepegawaian()
    {
        return $this->belongsTo(StatusKepegawaian::class, 'status_kepeg_id');
    }
    
    /**
     * Status Kerja.
     */
    public function status_kerja()
    {
        return $this->belongsTo(StatusKerja::class, 'status_kerja_id');
    }
    
    /**
     * Golongan.
     */
    public function golongan()
    {
        return $this->belongsTo(Golongan::class, 'golongan_id');
    }
    
    /**
     * Golru.
     */
    public function golru()
    {
        return $this->belongsTo(Golru::class, 'golru_id');
    }
    
    /**
     * Jabatan Fungsional.
     */
    public function jabfung()
    {
        return $this->belongsTo(GrupJabatan::class, 'jabfung_id');
    }
    
    /**
     * Jabatan Struktural.
     */
    public function jabstruk()
    {
        return $this->belongsTo(GrupJabatan::class, 'jabstruk_id');
    }
    
    /**
     * Unit.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Mutasi.
     */
    public function mutasi()
    {
        return $this->hasMany(Mutasi::class)->orderBy('tahun','desc')->orderBy('bulan','desc')->orderBy('tmt','desc')->orderBy('gaji_pokok_id','desc');
    }

    /**
     * Mutasi Detail.
     */
    public function mutasi_detail()
    {
        return $this->hasManyThrough(MutasiDetail::class, Mutasi::class, 'pegawai_id', 'mutasi_id', 'id', 'id');
    }

    /**
     * SPKGB.
     */
    public function spkgb()
    {
        return $this->hasManyThrough(SPKGB::class, Mutasi::class, 'pegawai_id', 'mutasi_id', 'id', 'id');
    }

    /**
     * Remun Gaji.
     */
    public function remun_gaji()
    {
        return $this->hasMany(RemunGaji::class)->orderBy('tahun','desc')->orderBy('bulan','desc');
    }

    /**
     * Remun Insentif.
     */
    public function remun_insentif()
    {
        return $this->hasMany(RemunInsentif::class)->orderBy('tahun','desc')->orderBy('triwulan','desc');
    }

    /**
     * Lebih Kurang.
     */
    public function lebih_kurang()
    {
        return $this->hasMany(LebihKurang::class)->orderBy('tahun','desc')->orderBy('bulan','desc');
    }

    /**
     * Tunjangan Profesi.
     */
    public function tunjangan_profesi()
    {
        return $this->hasMany(TunjanganProfesi::class)->orderBy('tahun','desc')->orderBy('bulan','desc')->orderBy('angkatan_id','asc');
    }

    /**
     * Gaji.
     */
    public function gaji()
    {
        return $this->hasMany(Gaji::class)->orderBy('tahun','desc')->orderBy('bulan','desc');
    }

    /**
     * Gaji Non ASN
     */
    public function gaji_non_asn()
    {
        return $this->hasMany(GajiNonASN::class)->orderBy('tahun','desc')->orderBy('bulan','desc');
    }

    /**
     * Uang Makan.
     */
    public function uang_makan()
    {
        return $this->hasMany(UangMakan::class)->orderBy('tahun','desc')->orderBy('bulan','desc');
    }

    /**
     * Satyalancana Karya Satya.
     */
    public function slks()
    {
        return $this->hasMany(SLKSDetail::class);
    }
}