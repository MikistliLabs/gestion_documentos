<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;

class EmpresaController extends Controller
{
    public function index(){
        $empresas = Empresa::all();
        return view('empresas.index', compact('empresas'));
    }

    public function store(Request $request){
        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);
        Empresa::create($request->only('nombre'));

        return redirect()->route('empresas.index')->with('success', 'Empresa creada con éxito.');
    }

    public function edit($id){
        $empresa = Empresa::findOrFail($id);
        return view('empresas.edit', compact('empresa'));
    }
    public function update(Request $request, $id){
        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        $empresa = Empresa::findOrFail($id);
        $empresa->update($request->only('nombre'));

        return redirect()->route('empresas.index')->with('success', 'Empresa actualizada con éxito.');
    }

    public function destroy($id){
        $empresa = Empresa::findOrFail($id);
        $empresa->delete();
        return redirect()->route('empresas.index')->with('success', 'Empresa eliminada.');
    }
}
