<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    public $timestamps = false;

    use HasFactory;

    
    protected $fillable = [
        'user_id',
        'tutor_id',
        'tutor_status',
        'curriculum',
    ];
    
    public function tutor()
    {
        return $this->belongsTo(Tutor::class)->with('sede');
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
