<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UangLembur extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_uang_lembur';

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
     * Golru.
     */
    public function golru()
    {
        return $this->belongsTo(Golru::class, 'golru_id');
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
}