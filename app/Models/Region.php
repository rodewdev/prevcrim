<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $table = 'regiones';
    protected $fillable = ['nombre'];

    public function comunas()
    {
        return $this->hasMany(Comuna::class, 'region_id');
    }

    public function delitos()
    {
        return $this->hasMany(Delito::class, 'region_id');
    }
}
