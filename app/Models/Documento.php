<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Documento extends Model
{
    protected $table = 'documentos';
    protected $primaryKey = 'id_documento';
    protected $fillable = ['id_carpeta', 'id_tipo_documento', 'nombre', 'archivo'];

    public function carpeta(){
        return $this->belongsTo(Carpeta::class, 'id_carpeta', 'id_carpeta');
    }
}
