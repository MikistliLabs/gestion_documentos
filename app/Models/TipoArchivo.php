<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoArchivo extends Model
{
    // use HasFactory;
    protected $table = 'tipos_archivos';
    protected $primaryKey = 'id_tipo';
    protected $fillable = ['nombre'];

    public function documentos(){
        return $this->hasMany(Documento::class, 'id_tipo_documento');
    }
}
