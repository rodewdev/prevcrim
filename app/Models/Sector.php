<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    use HasFactory;

    protected $table = 'sectores';
    protected $fillable = ['nombre'];

   /* public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'institucion_id');
    }
    public function comuna()
    {
        return $this->belongsTo(Comuna::class, 'comuna_id');
    }*/
}
