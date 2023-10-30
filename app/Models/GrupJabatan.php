<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupJabatan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_grup_jabatan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];
    
    /**
     * Jenis.
     */
    public function jenis()
    {
        return $this->belongsTo(JenisJabatan::class, 'jenis_id');
    }

    /**
     * Jabatan.
     */
    public function jabatan()
    {
        return $this->hasMany(Jabatan::class, 'grup_id');
    }

    /**
     * Pegawai Berdasarkan Jabatan Fungsional.
     */
    public function pegawai_jabfung()
    {
        return $this->hasMany(Pegawai::class, 'jabfung_id');
    }

    /**
     * Pegawai Berdasarkan Jabatan Struktural.
     */
    public function pegawai_jabstruk()
    {
        return $this->hasMany(Pegawai::class, 'jabstruk_id');
    }
}