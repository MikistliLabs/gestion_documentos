<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarpetaController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\ApiController;


Route::get('/', [CarpetaController::class, 'index'])->name('home');
// carpetas
Route::post('/carpetas', [CarpetaController::class, 'store'])->name('carpetas.store');
Route::get('carpetas/{id}/edit', [CarpetaController::class, 'edit'])->name('carpetas.edit');
Route::put('carpetas/{id}', [CarpetaController::class, 'update'])->name('carpetas.update');
Route::delete('carpetas_baja/{id}', [CarpetaController::class, 'destroyCarpeta'])->name('carpetas.destroy');
// documentos
Route::post('/documentos', [DocumentoController::class, 'store'])->name('documentos.store');
Route::put('documentos/{id}', [DocumentoController::class, 'update'])->name('documentos.update');
Route::delete('/documentos_baja/{id}', [DocumentoController::class, 'destroyDocumento'])->name('documentos.destroy');

Route::get('/api/direcciones/{empresaId}', [ApiController::class, 'direcciones']);
Route::get('/api/areas/{direccionId}', [ApiController::class, 'areas']);
Route::get('/api/carpeta/{areaId}', [ApiController::class, 'carpetas']);

// Árbol dinámico
Route::get('/api/tree', [CarpetaController::class, 'treeData']);

/**
 * Inicia empresas
*/
// Listar empresas
Route::get('empresas', [EmpresaController::class, 'index'])->name('empresas.index');

// Guardar nueva empresa
Route::post('empresas', [EmpresaController::class, 'store'])->name('empresas.store');

// Actualizar empresa
Route::put('empresas/{id}', [EmpresaController::class, 'update'])->name('empresas.update');

// Eliminar empresa
Route::delete('empresas/{id}', [EmpresaController::class, 'destroy'])->name('empresas.destroy');
/**
 * Termina empresas
 */

