<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SPKGB extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_spkgb';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];
    
    /**
     * Mutasi.
     */
    public function mutasi()
    {
        return $this->belongsTo(Mutasi::class, 'mutasi_id');
    }
    
    /**
     * Mutasi Sebelum.
     */
    public function mutasi_sebelum()
    {
        return $this->belongsTo(Mutasi::class, 'mutasi_sebelum_id');
    }
    
    /**
     * Pegawai.
     */
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
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
     * TTD.
     */
    public function ttd()
    {
        return $this->belongsTo(Pegawai::class, 'ttd_id');
    }
}