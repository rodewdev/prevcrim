<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatrullajeAsignacion extends Model
{
    use HasFactory;

    // Especifica el nombre correcto de la tabla
    protected $table = 'patrullaje_asignaciones';

    // Define las columnas que se pueden llenar masivamente
    protected $fillable = [
        'comuna_id',
        'sector_id',
        'institucion_id',
        'user_id',
        'prioridad',
        'fecha_inicio',
        'fecha_fin',
        'observaciones',
        'activo'
    ];

    // Relaciones
    public function comuna()
    {
        return $this->belongsTo(Comuna::class);
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}