<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delincuente extends Model
{
    use HasFactory;

    protected $table = 'delincuentes';

    protected $fillable = [
        'rut', 'nombre', 'apellidos', 'alias', 'domicilio', 'estado', 'foto',
        'ultimo_lugar_visto', 'telefono_fijo', 'celular', 'email', 'fecha_nacimiento'
    ];

    public function delitos()
    {
        return $this->belongsToMany(Delito::class, 'delincuente_delito')->withPivot('fecha_comision', 'observaciones')->withTimestamps();
    }

    public function familiares()
    {
        return $this->belongsToMany(Delincuente::class, 'delincuente_familiar', 'delincuente_id', 'familiar_id')
            ->withPivot('parentesco')
            ->withTimestamps();
    }
}
