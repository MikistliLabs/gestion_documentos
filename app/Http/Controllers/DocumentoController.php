<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\Documento;
use App\models\Carpeta;

class DocumentoController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'id_carpeta' => 'required|exists:carpetas,id_carpeta',
            'id_tipo_documento' => 'required|exists:tipos_archivos,id_tipo',
            'nombre' => 'required|string|max:255',
            'archivo' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:2048'
        ]);
        // Recuperamos el nombre de la carpeta
        $carpeta = Carpeta::findOrFail($request->id_carpeta);
        $nombreCarpeta = str_replace(' ', '_', $carpeta->nombre);
        // $path = $request->file('archivo')->store('documentos', 'public');
        //
        $nombreArchivo = str_replace(' ', '_', $request->nombre) . '.' . $request->file('archivo')->getClientOriginalExtension();
        $path = $request->file('archivo')->storeAs('app/public/' . $nombreCarpeta, $nombreArchivo, 'public');
        $rutaArchivo = storage_path($path);
        if (!file_exists($rutaArchivo)) {
            Documento::create([
                'id_carpeta' => $request->id_carpeta,
                'id_tipo_documento' => $request->id_tipo_documento,
                'nombre' => $request->nombre,
                'archivo' => $path
            ]);
            return back()->with('success', 'Documento cargadp con éxito.');
        } else {
            // El archivo no existe
            return back()->with('error', 'Documento ya existente.');
        }
        // return back()->with('success', 'Documento subido con éxito.');
    }
    public function update(Request $request, $id){
        $request->validate(['nombre' => 'required']);
        $item = Carpeta::findOrFail($id);
        $item->update($request->all());
        return back()->with('success', 'Actualizado correctamente');
    }

    public function destroy($id){
        Carpeta::destroy($id);
        return back()->with('success', 'Eliminado correctamente');
    }
}
