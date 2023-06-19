<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{
    protected $table = 'tutores';
    public $timestamps = false;
    
    protected $fillable = [
        'dni',
        'nombre',
        'apellidos',
        'telefono',
        'email',
        'sede_id'
    ];

    public function alumnos()
    {
        return $this->hasMany(Alumno::class);
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

}
