<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiDetail extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_mutasi_detail';

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
     * Jabatan.
     */
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }
    
    /**
     * Jabatan Dasar.
     */
    public function jabatan_dasar()
    {
        return $this->belongsTo(JabatanDasar::class, 'jabatan_dasar_id');
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
    
    /**
     * Angkatan.
     */
    public function angkatan()
    {
        return $this->belongsTo(Angkatan::class, 'angkatan_id');
    }

    /**
     * Mutasi Koorprodi.
     */
    public function koorprodi()
    {
        return $this->hasMany(MutasiKoorprodi::class);
    }
}