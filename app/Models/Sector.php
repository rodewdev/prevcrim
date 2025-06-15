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
    }*/
    public function comuna()
    {
        return $this->belongsTo(Comuna::class, 'comuna_id');
    }

    public function delitos()
    {
        return $this->hasMany(Delito::class, 'sector_id');
    }
    
    public function patrullajesActivos()
    {
        return $this->hasMany(PatrullajeAsignacion::class, 'sector_id')
            ->where('activo', true)
            ->where(function ($query) {
                $query->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', now());
            });
    }
}
