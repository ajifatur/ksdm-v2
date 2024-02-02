<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiKoorprodi extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_mutasi_koorprodi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];
    
    /**
     * Mutasi Detail.
     */
    public function mutasi_detail()
    {
        return $this->belongsTo(MutasiDetail::class, 'mutasi_detail_id');
    }
    
    /**
     * Prodi.
     */
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }
}