<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perubahan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_perubahan';

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
     * Perubahan.
     */
    public function perubahan()
    {
        return $this->belongsTo(Perubahan::class, 'perubahan_id');
    }
    
    /**
     * SK.
     */
    public function sk()
    {
        return $this->belongsTo(SK::class, 'sk_id');
    }
    
    /**
     * Pejabat.
     */
    public function pejabat()
    {
        return $this->belongsTo(Pejabat::class, 'pejabat_id');
    }
}