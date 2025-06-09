<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comuna extends Model
{
    use HasFactory;

    protected $table = 'comunas';
    protected $fillable = ['nombre', 'region_id'];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function sectores()
    {
        return $this->hasMany(Sector::class, 'comuna_id');
    }

    public function delincuentes()
    {
        return $this->hasMany(Delincuente::class, 'comuna_id');
    }

    public function delitos()
    {
        return $this->hasMany(Delito::class, 'comuna_id');
    }
}
