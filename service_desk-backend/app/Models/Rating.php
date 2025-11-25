<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $table = 'tblm_rating';
    protected $primaryKey = 'id_rating';

    protected $fillable = [
        'nama_rating',
        'nilai_rating', 
        'rating_description',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_rating', 'id_rating');
    }
}