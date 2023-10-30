<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mutasi extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_mutasi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];
    
    /**
     * Pegawai.
     */
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
    
    /**
     * SK.
     */
    public function sk()
    {
        return $this->belongsTo(SK::class, 'sk_id');
    }
    
    /**
     * Jenis Mutasi.
     */
    public function jenis()
    {
        return $this->belongsTo(JenisMutasi::class, 'jenis_id');
    }
    
    /**
     * Status Kepegawaian.
     */
    public function status_kepegawaian()
    {
        return $this->belongsTo(StatusKepegawaian::class, 'status_kepeg_id');
    }
    
    /**
     * Golru.
     */
    public function golru()
    {
        return $this->belongsTo(Golru::class, 'golru_id');
    }
    
    /**
     * Gaji Pokok.
     */
    public function gaji_pokok()
    {
        return $this->belongsTo(GajiPokok::class, 'gaji_pokok_id');
    }

    /**
     * Mutasi Detail.
     */
    public function detail()
    {
        return $this->hasMany(MutasiDetail::class)->orderBy('status','desc');
    }

    /**
     * Perubahan.
     */
    public function perubahan()
    {
        return $this->hasOne(Perubahan::class);
    }
}