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
            'id_padre' => 'nullable|exists:carpetas,id_carpeta'
        ]);

        $carpeta = Carpeta::findOrFail($id);

        $nombreActual = str_replace(' ', '_', $carpeta->nombre);
        $nuevoNombre  = str_replace(' ', '_', $request->nombre);

        // Si cambió el nombre, renombrar físicamente
        if ($nombreActual !== $nuevoNombre) {
            // Ruta actual
            if ($carpeta->id_padre) {
                $padre = Carpeta::find($carpeta->id_padre);
                $rutaActual = storage_path('app/public/' . str_replace(' ', '_', $padre->nombre) . '/' . $nombreActual);
                $rutaNueva  = storage_path('app/public/' . str_replace(' ', '_', $padre->nombre) . '/' . $nuevoNombre);
            } else {
                $rutaActual = storage_path('app/public/' . $nombreActual);
                $rutaNueva  = storage_path('app/public/' . $nuevoNombre);
            }

            // Verificar existencia
            if (File::exists($rutaActual) && !File::exists($rutaNueva)) {
                File::move($rutaActual, $rutaNueva);
            } else {
                return back()->with('error', 'No se pudo renombrar la carpeta física.');
            }
        }

        // Guardar cambios en BD
        $carpeta->update([
            'id_area' => $request->id_area,
            'id_padre' => $request->id_padre,
            'nombre' => $request->nombre
        ]);

        return back()->with('success', 'Carpeta actualizada correctamente.');
    }

    public function destroy($id){
        $carpeta = Carpeta::findOrFail($id);
        $carpeta->delete();
        return back()->with('success', 'Carpeta eliminada.');
    }
    // Para jsTree: retorna todas las carpetas y subcarpetas
    public function treeData(Request $request){
        // $data = Carpeta::with('subcarpetas')->whereNull('id_padre')->get();
        // return response()->json($this->formatTree($data));
        $query = Carpeta::with(['subcarpetas', 'documentos']);
        // filtrar por documentos
        if ($request->filled('nombre')) {
            $query->whereHas('documentos', function($q) use ($request){
                $q->where('nombre', 'like', '%'.$requets->nombre.'%');
            });
        }
        // Filtro de empresa
        if ($request->filled('empresa')) {
            $query->whereHas('area.direccion.empresa', function ($q) use ($request) {
                $q->where('empresas.id_empresa', $request->empresa);
            });
        }
        // Filtro por dirección
        if ($request->filled('direccion')) {
            $query->whereHas('area.direccion', function ($q) use ($request) {
                $q->where('direcciones.id_direccion', $request->direccion);
            });
        }
        // Filtro por área
        if ($request->filled('area')) {
            $query->where('areas.id_area', $request->area);
        }
        $data = $query->whereNull('id_padre')->get();
        return response()->json($this->formatTree($data));
    }

    // private function formatTree($carpetas){
    //     $result = [];

    //     foreach ($carpetas as $carpeta) {
    //         $buttonsCarpeta = '
    //             <span class="tree-actions">
    //                 <a href="#" class="icon-action edit btn-editar-carpeta" data-id="' . $carpeta->id_carpeta . '" data-nombre="' . e($carpeta->nombre) . '" title="Editar"><i class="fa-solid fa-pen"></i></a>
    //                 <form method="POST" action="' . url("carpetas/{$carpeta->id_carpeta}") . '" style="display:inline">
    //                     ' . csrf_field() . method_field('DELETE') . '
    //                     <button type="submit" class="icon-action" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
    //                 </form>
    //             </span>
    //         ';

    //         $carpetaNode = [
    //             'id' => 'carpeta_' . $carpeta->id_carpeta,
    //             'text' => '<span class="tree-label">' . e($carpeta->nombre) . '</span>' . $buttonsCarpeta,
    //             'children' => []
    //         ];

    //         foreach ($carpeta->documentos as $doc) {
    //             $buttonsDoc = '
    //                 <span class="tree-actions">
    //                     <a href="#" class="icon-action edit btn-editar-documento" data-id="' . $doc->id_documento . '" data-nombre="' . e($doc->nombre) . '" title="Editar"><i class="fa-solid fa-pen"></i></a>
    //                     <form method="POST" action="' . url("documentos/{$doc->id_documento}") . '" style="display:inline">
    //                         ' . csrf_field() . method_field('DELETE') . '
    //                         <button type="submit" class="icon-action delete" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
    //                     </form>
    //                 </span>
    //             ';

    //             $carpetaNode['children'][] = [
    //                 'id' => 'doc_' . $doc->id_documento,
    //                 'text' => '<span class="tree-label">📄 ' . e($doc->nombre) . '</span>' . $buttonsDoc
    //             ];
    //         }

    //         if ($carpeta->subcarpetas->count() > 0) {
    //             $carpetaNode['children'] = array_merge(
    //                 $carpetaNode['children'],
    //                 $this->formatTree($carpeta->subcarpetas)
    //             );
    //         }

    //         $result[] = $carpetaNode;
    //     }

    //     return $result;
    // }
    private function formatTree($carpetas){
        $result = [];
        foreach ($carpetas as $carpeta) {
            $carpetaNode = [
                'id' => 'carpeta_' . $carpeta->id_carpeta,
                'text' => '📁 ' . e($carpeta->nombre) .
                    ' <i class="jstree-themeicon fa fa-pen btn-edit"
                        data-type="carpeta" data-id="' . $carpeta->id_carpeta . '"
                        title="Editar"></i>
                    <i class="jstree-themeicon fa fa-trash btn-delete"
                        data-type="carpeta" data-id="' . $carpeta->id_carpeta . '"
                        title="Eliminar"></i>',
                'children' => []
            ];

            // Agregar documentos como hijos
            foreach ($carpeta->documentos as $doc) {
                $carpetaNode['children'][] = [
                    'id' => 'doc_' . $doc->id_documento,
                    'text' => '📄 ' . e($doc->nombre) .
                        ' <i class="jstree-themeicon fa fa-pen btn-edit"
                            data-type="documento" data-id="' . $doc->id_documento . '"
                            title="Editar"></i>
                        <i class="jstree-themeicon fa fa-trash btn-delete"
                            data-type="documento" data-id="' . $doc->id_documento . '"
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

}
