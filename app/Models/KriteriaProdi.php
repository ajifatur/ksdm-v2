<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KriteriaProdi extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_kriteria_prodi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];
    
    /**
     * Prodi.
     */
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }
}