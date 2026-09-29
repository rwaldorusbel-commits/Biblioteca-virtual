<?php

namespace App\Http\Controllers;

use App\Models\Autor;
use App\Models\Editor;
use App\Models\Libro;
use Illuminate\Http\Request;

class LibroController extends Controller
{
    public function index()
    {
        $libros = Libro::all();
        return view('libros.index', compact('libros'));
    }

    public function create()
    {
        $autores  = Autor::all();
        $editores = Editor::all();
        return view('libros.create', compact('autores', 'editores'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'Titulo'    => 'required|string|max:255',
            'genero'    => 'nullable|string|max:255',
            'Tipo'      => 'nullable|string|max:255',
            'ID_autor'  => 'nullable|integer',
            'ID_editor' => 'nullable|integer',
            'imagen'    => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('imagen')) {
            $datos['imagen'] = $request->file('imagen')->store('portadas', 'public');
        }

        Libro::create($datos);

        return redirect()->route('libros.index')->with('ok', 'Libro creado');
    }

    public function edit(Libro $libro)
    {
        $autores  = Autor::all();
        $editores = Editor::all();
        return view('libros.edit', compact('libro', 'autores', 'editores'));
    }

    public function update(Request $request, Libro $libro)
    {
        $datos = $request->validate([
            'Titulo'    => 'required|string|max:255',
            'genero'    => 'nullable|string|max:255',
            'Tipo'      => 'nullable|string|max:255',
            'ID_autor'  => 'nullable|integer',
            'ID_editor' => 'nullable|integer',
            'imagen'    => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('imagen')) {
            $datos['imagen'] = $request->file('imagen')->store('portadas', 'public');
        }

        $libro->update($datos);

        return redirect()->route('libros.index')->with('ok', 'Libro actualizado');
    }

    public function destroy(Libro $libro)
    {
        $libro->delete();
        return redirect()->route('libros.index')->with('ok', 'Libro eliminado');
    }

    public function show(Libro $libro)
{
    return redirect()->route('libros.index');
}
}
