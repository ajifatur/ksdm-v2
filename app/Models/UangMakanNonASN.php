<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UangMakanNonASN extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_uang_makan_non_asn';

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
     * Status Kepegawaian.
     */
    public function status_kepegawaian()
    {
        return $this->belongsTo(StatusKepegawaian::class, 'status_kepeg_id');
    }
}