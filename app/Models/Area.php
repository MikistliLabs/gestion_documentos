<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    // use HasFactory;
    protected $table = 'areas';
    protected $primarKey = 'id_area';
    protected $fillable = ['id_empresa'];

    public function direccion() {
        return $this->belongsTo(Direccion::class, 'id_direccion', 'id_direccion');
    }
    public function carpetas(){
        return $this->hasMany(Carpeta::class, 'id_area');
    }
}
