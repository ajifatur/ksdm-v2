<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GajiKontrak extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_gaji_kontrak';

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
     * Unit.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
    
    /**
     * SK.
     */
    public function sk()
    {
        return $this->belongsTo(SKKontrak::class, 'sk_id');
    }
    
    /**
     * Jenis Gaji Kontrak.
     */
    public function jenis()
    {
        return $this->belongsTo(JenisGajiKontrak::class, 'jenis_id');
    }
}