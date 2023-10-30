<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemunGaji extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_remun_gaji';

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
     * Status Kepegawaian.
     */
    public function status_kepegawaian()
    {
        return $this->belongsTo(StatusKepegawaian::class, 'status_kepeg_id');
    }
    
    /**
     * Jabatan Dasar.
     */
    public function jabatan_dasar()
    {
        return $this->belongsTo(JabatanDasar::class, 'jabatan_dasar_id');
    }
    
    /**
     * Jabatan.
     */
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }
    
    /**
     * Unit.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
    
    /**
     * Layer.
     */
    public function layer()
    {
        return $this->belongsTo(Layer::class, 'layer_id');
    }
}