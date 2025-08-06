<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Carpeta;

class CarpetaController extends Controller
{
    public function index(){
        $carpetas = Carpeta::with('subcarpetas')->whereNull('id_padre')->get();
        return view('carpetas.index', compact('carpetas'));
    }
    public function store(Request $request){
        $request->validate([
            'id_area' => 'required|exists:areas,id_area',
            'nombre' => 'required|string|max:255',
            'id_padre' => 'nullable|exists:carpetas,id_carpeta'
        ]);
        $nombre_carpeta = str_replace(' ', '_', $request->nombre); // convertimos los espacios en blanco en _ para el nombre de las carpetas
        if ($request->id_padre) {
            $carpeta_padre = Carpeta::with('carpetaPadre')->where('id_carpeta',$request->id_padre)->first();
            $carpeta_hija = Carpeta::with('subcarpetas')->where('id_padre',$request->id_padre)
                ->where('nombre', $request->nombre)->get();
            $rutaCarpeta_hija = storage_path('app/public/'.str_replace(' ', '_', $carpeta_padre->nombre).'/'.str_replace(' ', '_', $request->nombre)); // Ruta dentro de storage/app/public
            if (!File::exists($rutaCarpeta_hija)) {
                File::makeDirectory($rutaCarpeta_hija, 0777, true); // Crea la carpeta con permisos 777 (puedes ajustar los permisos)
                Carpeta::create($request->only('id_padre', 'id_area', 'nombre'));
            }else{
                return back()->with('error', 'Subcarpeta ya existente.');
            }
        }else{
            $rutaCarpeta = storage_path('app/public/'.$nombre_carpeta); // Ruta dentro de storage/app/public
            if (!File::exists($rutaCarpeta)) {
                File::makeDirectory($rutaCarpeta, 0777, true); // Crea la carpeta con permisos 777 (puedes ajustar los permisos)
                Carpeta::create($request->only('id_padre', 'id_area', 'nombre'));
            }else{
                return back()->with('error', 'Carpeta ya existente.');
            }
        }
        return back()->with('success', 'Carpeta creada con éxito.');
    }
    public function edit($id){
        $carpeta = Carpeta::findOrFail($id);

        // Todas las empresas
        $empresas = \App\Models\Empresa::all();

        // Direcciones filtradas por empresa
        $direcciones = \App\Models\Direccion::where('id_empresa', $carpeta->area->direccion->id_empresa)->get();

        // Áreas filtradas por dirección
        $areas = \App\Models\Area::where('id_direccion', $carpeta->area->id_direccion)->get();

        // Carpetas padre (mismo área, excepto ella misma)
        $carpetasPadre = Carpeta::where('id_area', $carpeta->id_area)
            ->where('id_carpeta', '!=', $carpeta->id_carpeta)
            ->get();

        return response()->json([
            'carpeta' => $carpeta,
            'empresas' => $empresas,
            'direcciones' => $direcciones,
            'areas' => $areas,
            'carpetasPadre' => $carpetasPadre
        ]);
    }

    public function update(Request $request, $id){
        $request->validate([
            'id_area' => 'required|exists:areas,id_area',
            'nombre' => 'required|string|max:255',
            'carpeta_padre_edit' => 'nullable|exists:carpetas,id_carpeta'
        ]);

        $carpeta = Carpeta::findOrFail($id);

        $padreActualId = $carpeta->id_padre;
        $padreNuevoId  = $request->carpeta_padre_edit;
        $nombreActual  = str_replace(' ', '_', $carpeta->nombre);
        $nuevoNombre   = str_replace(' ', '_', $request->nombre);

        // Función para construir ruta física
        $rutaCarpeta = function ($padreId, $nombre) {
            $rutaBase = storage_path('app/public/');
            if ($padreId) {
                $padre = Carpeta::find($padreId);
                if (!$padre) return null;
                return $rutaBase . str_replace(' ', '_', $padre->nombre) . '/' . $nombre;
            }
            return $rutaBase . $nombre;
        };

        // Si cambió el padre o el nombre
        if ($padreActualId !== $padreNuevoId || $nombreActual !== $nuevoNombre) {
            $rutaActual = $rutaCarpeta($padreActualId, $nombreActual);
            $rutaNueva  = $rutaCarpeta($padreNuevoId, $nuevoNombre);

            if ($rutaActual && $rutaNueva && File::exists($rutaActual) && !File::exists($rutaNueva)) {
                File::move($rutaActual, $rutaNueva);
            } else {
                return back()->with('error', 'No se pudo renombrar/mover la carpeta física.');
            }
        }

        // Guardar cambios en BD
        $carpeta->update([
            'id_area' => $request->id_area,
            'id_padre' => $padreNuevoId,
            'nombre' => $request->nombre
        ]);

        return back()->with('success', 'Carpeta actualizada correctamente.');
    }

    // Para jsTree: retorna todas las carpetas y subcarpetas
    public function treeData(Request $request){
        $query = Carpeta::query()
        ->select(
            'carpetas.*',
            'empresas.nombre as empresa_nombre',
            'empresas.id_empresa as id_empresa',
            'direcciones.nombre as direccion_nombre',
            'direcciones.id_direccion as id_direccion',
            'areas.nombre as area_nombre'
        )
        ->leftJoin('areas', 'areas.id_area', '=', 'carpetas.id_area')
        ->leftJoin('direcciones', 'direcciones.id_direccion', '=', 'areas.id_direccion')
        ->leftJoin('empresas', 'empresas.id_empresa', '=', 'direcciones.id_empresa')
        ->with([
            'documentos',
            'subcarpetas' => function ($q) {
                $q->select(
                    'carpetas.*',
                    'empresas.nombre as empresa_nombre',
                    'empresas.id_empresa as id_empresa',
                    'direcciones.nombre as direccion_nombre',
                    'direcciones.id_direccion as id_direccion',
                    'areas.nombre as area_nombre'
                )
                ->leftJoin('areas', 'areas.id_area', '=', 'carpetas.id_area')
                ->leftJoin('direcciones', 'direcciones.id_direccion', '=', 'areas.id_direccion')
                ->leftJoin('empresas', 'empresas.id_empresa', '=', 'direcciones.id_empresa')
                ->with(['documentos', 'subcarpetas']);
            }
        ]);
        // Filtro por nombre de documento
        if ($request->filled('nombre')) {
            $query->whereHas('documentos', function($q) use ($request){
                $q->where('nombre', 'like', '%'.$request->nombre.'%');
            });
        }
        // Filtro por empresa
        if ($request->filled('empresa')) {
            $query->whereHas('empresa', function ($q) use ($request) {
                $q->where('id_empresa', $request->empresa);
            });
        }

        // Filtro por dirección
        if ($request->filled('direccion')) {
            $query->whereHas('direccion', function ($q) use ($request) {
                $q->where('id_direccion', $request->direccion);
            });
        }

        // Filtro por área
        if ($request->filled('area')) {
            $query->whereHas('area', function ($q) use ($request) {
                $q->where('id_area', $request->area);
            });
        }

        // Solo carpetas raíz
        $data = $query->whereNull('id_padre')->get();
        return response()->json($this->formatTree($data));
    }

    private function formatTree($carpetas){
        $result = [];

        foreach ($carpetas as $carpeta) {
            $carpetaNode = [
                'id' => 'carpeta_' . $carpeta->id_carpeta,
                'text' => e($carpeta->nombre) .
                    ' <i class="jstree-themeicon fa fa-pen btn-edit btn-editar-carpeta"
                        data-type="carpeta"
                        data-name="'.$carpeta->nombre.'"
                        data-id="' . $carpeta->id_carpeta . '"
                        data-id_padre="'.$carpeta->id_padre.'"
                        data-id_empresa="' . $carpeta->id_empresa . '"
                        data-id_direccion="' . $carpeta->id_direccion . '"
                        data-id_area="' . $carpeta->id_area . '"
                        title="Editar"></i>
                    <i class="jstree-themeicon fa fa-trash btn-delete"
                        data-type="carpeta"
                        data-name="'.$carpeta->nombre.'"
                        data-id="' . $carpeta->id_carpeta . '"
                        data-id_empresa="' . $carpeta->id_empresa . '"
                        data-id_direccion="' . $carpeta->id_direccion . '"
                        data-id_area="' . $carpeta->id_area . '"
                        title="Eliminar"></i>',
                'children' => []
            ];

            // Agregar documentos como hijos
            foreach ($carpeta->documentos as $doc) {
                $carpetaNode['children'][] = [
                    'id' => 'doc_' . $doc->id_documento,
                    'text' => '📄 ' . e($doc->nombre) .
                        ' <i class="jstree-themeicon fa fa-pen btn-edit btn-editar-documento"
                            data-type="documento"
                            data-id="' . $doc->id_documento . '"
                            data-name="' . e($doc->nombre) . '"
                            data-id_carpeta="' . $carpeta->id_carpeta . '"
                            data-tipo="' . $doc->id_tipo_documento . '"
                            title="Editar"></i>
                        <i class="jstree-themeicon fa fa-trash btn-delete"
                            data-type="documento"
                            data-id="' . $doc->id_documento . '"
                            title="Eliminar"></i>'
                ];
            }
            // Subcarpetas
            if ($carpeta->subcarpetas->count() > 0) {
                $carpetaNode['children'] = array_merge(
                    $carpetaNode['children'],
                    $this->formatTree($carpeta->subcarpetas)
                );
            }

            $result[] = $carpetaNode;
        }
        return $result;
    }
    private function buildPath($carpeta){
        $ruta = [];
        while ($carpeta) {
            array_unshift($ruta, str_replace(' ', '_', $carpeta->nombre));
            $carpeta = $carpeta->padre; // relación belongsTo
        }
        return implode('/', $ruta);
    }
    public function destroyCarpeta(request $request){
        $id = $request->id;
        $carpeta = Carpeta::with(['documentos', 'subcarpetas'])->findOrFail($id);
        // Eliminar documentos dentro de la carpeta
        foreach ($carpeta->documentos as $documento) {
            $rutaDoc = storage_path('app/public/' . $this->buildPath($carpeta) . '/' . $documento->archivo);
            if (File::exists($rutaDoc)) {
                File::delete($rutaDoc);
            }
            $documento->delete();
        }
        // Eliminar subcarpetas recursivamente
        foreach ($carpeta->subcarpetas as $subcarpeta) {
            $this->destroyCarpeta($subcarpeta->id_carpeta);
        }
        // Eliminar carpeta física
        $rutaCarpeta = storage_path('app/public/' . $this->buildPath($carpeta));
        if (File::exists($rutaCarpeta)) {
            File::deleteDirectory($rutaCarpeta);
        }
        // Eliminar carpeta de la BD
        $carpeta->delete();

        return back()->with('success', 'Carpeta eliminada correctamente.');
    }
}
