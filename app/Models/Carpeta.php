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
