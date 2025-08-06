<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    // use HasFactory;
    protected $table = 'direcciones';
    protected $priamryKey = 'id_direcciones';
    protected $fillable = ['id_empresa', 'nombre'];

    public function empresa() {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }
    public function areas(){
        return $this->hasMany(Area::class,'id_area');
    }
}
