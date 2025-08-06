<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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
        $nombreArchivo = str_replace(' ', '_', $request->nombre) . '.' . $request->file('archivo')->getClientOriginalExtension();
        $path = $request->file('archivo')->storeAs('public/' . $nombreCarpeta, $nombreArchivo, 'public');
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
            return back()->with('error', 'Documento ya existente.');
        }
    }
    public function update(Request $request, $id){
        $request->validate([
            'nombre' => 'required|string|max:255',
            'id_carpeta' => 'required|exists:carpetas,id_carpeta',
            'archivo' => 'nullable|file|max:20480',
        ]);

        $documento = Documento::findOrFail($id);
        $carpetaActual = Carpeta::findOrFail($documento->id_carpeta);
        $carpetaNueva = Carpeta::findOrFail($request->id_carpeta);

        $nombreActual = $documento->nombre;
        $nuevoNombre = $request->nombre;
        $archivoActual = $documento->archivo;
        $nuevoArchivo = $request->file('archivo');
        // Rutas completas
        $rutaCarpetaActual = storage_path('app/public/' . $this->buildPath($carpetaActual));
        $rutaCarpetaNueva = storage_path('app/public/' . $this->buildPath($carpetaNueva));

        $rutaArchivoActual = $rutaCarpetaActual . '/' . $archivoActual;
        // Caso 1: Subieron un archivo nuevo
        if ($nuevoArchivo) {
            if (!File::exists($rutaCarpetaNueva)) {
                File::makeDirectory($rutaCarpetaNueva, 0755, true);
            }
            if (File::exists($rutaArchivoActual)) {
                File::delete($rutaArchivoActual);
            }
            $nombreArchivoFisico = Str::slug(pathinfo($nuevoArchivo->getClientOriginalName(), PATHINFO_FILENAME), '_')
                . '.' . $nuevoArchivo->getClientOriginalExtension();

            $nuevoArchivo->storeAs('public/' . $this->buildPath($carpetaNueva), $nombreArchivoFisico);
            $documento->archivo = $nombreArchivoFisico;
        }
        // Caso 2: No subieron archivo pero cambia carpeta o nombre
        elseif ($carpetaActual->id_carpeta !== $carpetaNueva->id_carpeta || $nombreActual !== $nuevoNombre) {
            $extension = pathinfo($archivoActual, PATHINFO_EXTENSION);
            $nombreArchivoFisico = $archivoActual;
            if ($nombreActual !== $nuevoNombre) {
                $nombreArchivoFisico = Str::slug($nuevoNombre, '_') . '.' . $extension;
            }
            if (!File::exists($rutaCarpetaNueva)) {
                File::makeDirectory($rutaCarpetaNueva, 0777, true);
            }
            if (File::exists($rutaArchivoActual)) {
                File::move($rutaArchivoActual, $rutaCarpetaNueva . '/' . $nombreArchivoFisico);
                $documento->archivo = $nombreArchivoFisico;
            } else {
                return back()->with('error', 'No se encontró el archivo físico para mover.');
            }
        }
        // Actualizar datos
        $documento->nombre = $nuevoNombre;
        $documento->id_carpeta = $request->id_carpeta;
        $documento->save();

        return back()->with('success', 'Documento actualizado correctamente.');
    }

    private function buildPath(Carpeta $carpeta)
    {
        $segments = [];
        while ($carpeta) {
            array_unshift($segments, str_replace(' ', '_', $carpeta->nombre));
            $carpeta = $carpeta->id_padre ? Carpeta::find($carpeta->id_padre) : null;
        }
        return implode('/', $segments);
    }
    public function destroyDocumento($id){
        $documento = Documento::findOrFail($id);
        $carpeta = $documento->carpeta;
        // Ruta física del archivo
        $rutaArchivo = storage_path('app/public/' . $this->buildPath($carpeta) . '/' . $documento->archivo);
        // Borrar archivo físico si existe
        if (File::exists($rutaArchivo)) {
            File::delete($rutaArchivo);
        }
        // Borrar de la base de datos
        $documento->delete();
        return back()->with('success', 'Documento eliminado correctamente.');
    }
}
