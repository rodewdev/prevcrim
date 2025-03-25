<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delincuente extends Model
{
    use HasFactory;

    protected $table = 'delincuentes';

    protected $fillable = ['rut', 'nombre', 'alias', 'domicilio', 'estado', 'foto'];

    public function delitos()
    {
        return $this->belongsToMany(Delito::class, 'delincuente_delito')->withPivot('fecha_comision', 'observaciones');
    }
}
