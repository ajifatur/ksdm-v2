<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaktifanSerdos extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_pengaktifan_serdos';

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
     * Angkatan.
     */
    public function angkatan()
    {
        return $this->belongsTo(Angkatan::class, 'angkatan_id');
    }
    
    /**
     * Gaji Pokok.
     */
    public function gaji_pokok()
    {
        return $this->belongsTo(GajiPokok::class, 'gaji_pokok_id');
    }
}