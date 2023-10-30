<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LebihKurang extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_lebih_kurang';

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
     * Jabatan Terbayar.
     */
    public function jabatan_terbayar()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_terbayar_id');
    }
    
    /**
     * Jabatan Seharusnya.
     */
    public function jabatan_seharusnya()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_seharusnya_id');
    }
}