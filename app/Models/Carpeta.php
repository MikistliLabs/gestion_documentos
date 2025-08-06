<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carpeta extends Model
{
    // use HasFactory;
    protected $table = 'carpetas';
    protected $primaryKey = 'id_carpeta';
    protected $fillable = ['id_padre', 'id_area', 'nombre'];

    public function empresa(){
        // return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
        return $this->hasOneThrough(
            Empresa::class, // modelo final
            Direccion::class, // modelo intermedio
            'id_direccion', // FK en direccion → relaciona con empresa
            'id_empresa',   // FK en empresa
            'id_area',      // FK en carpeta → relaciona con direccion
            'id_empresa'    // PK en empresa
        );
    }

    public function direccion(){
        // return $this->belongsTo(Direccion::class, 'id_direccion', 'id_direccion');
        return $this->hasOneThrough(
            Direccion::class,
            Area::class,
            'id_area',        // FK en area → relaciona con direccion
            'id_direccion',   // FK en direccion
            'id_area',        // FK en carpeta
            'id_direccion'    // PK en direccion
        );
    }

    public function area(){
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }

    public function documento(){
        return $this->hasMany(Documento::class, 'id_carpeta');
    }
    // public function subcarpetas(){
    //     return $this->hasMany(Carpeta::class,'id_padre');
    // }
    public function carpetaPadre(){
        return $this->belongsTo(Carpeta::class, 'id_padre');
    }
    public function subcarpetas(){
        return $this->hasMany(Carpeta::class, 'id_padre', 'id_carpeta')->with('subcarpetas', 'documentos');
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'id_carpeta', 'id_carpeta');
    }
}
