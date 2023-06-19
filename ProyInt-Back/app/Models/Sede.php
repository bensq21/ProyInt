<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'empresa',
        'contacto',
        'telefono',
        'email',
    ];

    public function tutores()
    {
        return $this->hasMany(Tutor::class);
    }
}
