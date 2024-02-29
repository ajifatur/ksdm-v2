<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TunjanganProfesi extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_tunjangan_profesi';

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
     * Angkatan.
     */
    public function angkatan()
    {
        return $this->belongsTo(Angkatan::class, 'angkatan_id');
    }
    
    /**
     * Unit.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
    
    /**
     * Golongan.
     */
    public function golongan()
    {
        return $this->belongsTo(Golongan::class, 'golongan_id');
    }
    
    /**
     * Gaji Pokok.
     */
    public function gaji_pokok()
    {
        return $this->belongsTo(GajiPokok::class, 'gaji_pokok_id');
    }
}