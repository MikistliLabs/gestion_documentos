<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    // use HasFactory;
    protected $table = 'empresas';
    protected $primaryKey = 'id_empresa';
    protected $fillable = ['nombre'];

    public function direcciones(){
        return $this->hasMany(Direccion::class, 'id_empresa', 'id_empresa');
    }
}
