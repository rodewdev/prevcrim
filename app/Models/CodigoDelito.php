<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodigoDelito extends Model
{
    use HasFactory;
    protected $table = 'codigos_delitos';

    protected $fillable = ['codigo', 'descripcion'];

    public function delitos()
    {
        return $this->hasMany(Delito::class);
    }
}
