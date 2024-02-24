<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_jabatan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];
    
    /**
     * Grup Jabatan.
     */
    public function grup()
    {
        return $this->belongsTo(GrupJabatan::class, 'grup_id');
    }
    
    /**
     * SK.
     */
    public function sk()
    {
        return $this->belongsTo(SK::class, 'sk_id');
    }
    
    /**
     * Jabatan Dasar.
     */
    public function jabatan_dasar()
    {
        return $this->belongsTo(JabatanDasar::class, 'jabatan_dasar_id');
    }
}