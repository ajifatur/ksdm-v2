<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gaji extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_gaji';

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
     * Anak Satker.
     */
    public function anak_satker()
    {
        return $this->belongsTo(AnakSatker::class, 'anak_satker_id');
    }
    
    /**
     * Jenis Gaji.
     */
    public function jenis_gaji()
    {
        return $this->belongsTo(JenisGaji::class, 'jenis_id');
    }
}