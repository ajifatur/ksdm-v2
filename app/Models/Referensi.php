<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referensi extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_referensi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];
    
    /**
     * Jabatan Dasar.
     */
    public function jabatan_dasar()
    {
        return $this->belongsTo(JabatanDasar::class, 'jabatan_dasar_id');
    }
    
    /**
     * Layer.
     */
    public function layer()
    {
        return $this->belongsTo(Layer::class, 'layer_id');
    }
}