<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupStatusKepegawaian extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_grup_status_kepegawaian';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * Status Kepegawaian.
     */
    public function status_kepegawaian()
    {
        return $this->hasMany(StatusKepegawaian::class, 'grup_id');
    }
}