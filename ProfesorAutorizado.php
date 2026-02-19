<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfesorAutorizado extends Model
{
    use HasFactory;

    // Definimos la tabla explícitamente por si acaso
    protected $table = 'profesores_autorizados';

    protected $fillable = [
        'email',
        'estatus_registro',
        'condicion',
        'observaciones',
        'created_by',
    ];

    // Relación: Un profesor autorizado fue invitado por un Admin (User)
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}