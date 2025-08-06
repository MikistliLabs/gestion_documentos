<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\Empresa;
use App\models\Direccion;
use App\models\Area;
use App\models\Carpeta;

class ApiController extends Controller{

    public function direcciones($empresaId){
        return Direccion::where('id_empresa', $empresaId)->get();
    }
    public function areas($direccionId){
        return Area::where('id_direccion', $direccionId)->get();
    }
    public function carpetas($areaId){
        return Carpeta::where('id_area', $areaId)->get();
    }
}
