<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Golru extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_golru';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];
    
    /**
     * Golongan.
     */
    public function golongan()
    {
        return $this->belongsTo(Golongan::class, 'golongan_id');
    }

    /**
     * Gaji Pokok.
     */
    public function gaji_pokok()
    {
        return $this->hasMany(GajiPokok::class);
    }
}