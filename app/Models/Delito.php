<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delito extends Model
{
    use HasFactory;

    protected $table = 'delitos';

    protected $fillable = ['codigo', 'codigo_delito_id', 'descripcion', 'sector_id', 'comuna_id', 'region_id', 'fecha','user_id', 'institucion_id'];


    public function codigoDelito()
    {
        return $this->belongsTo(CodigoDelito::class);
    }
    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }

    public function comuna()
    {
        return $this->belongsTo(Comuna::class, 'comuna_id');
    }
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function delincuentes()
    {
        return $this->belongsToMany(Delincuente::class, 'delincuente_delito')->withPivot('fecha_comision', 'observaciones');
    }

    public function user()
{
    return $this->belongsTo(\App\Models\User::class, 'user_id');
}

    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'institucion_id');
    }
}
