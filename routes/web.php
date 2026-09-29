<?php

use App\Models\Autor;
use App\Models\Editor;
use App\Models\Libro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LibroController;

Route::resource('libros', LibroController::class)->except(['show']);

Route::resource('libros', LibroController::class);

Route::get('/', function (Request $request) {
    $libros = Libro::with(['autor', 'editor'])
        ->when($request->q, fn ($q, $v) => $q->where('Titulo', 'like', "%{$v}%"))
        ->when($request->genero, fn ($q, $v) => $q->where('genero', $v))
        ->when($request->tipo, fn ($q, $v) => $q->where('Tipo', $v))
        ->paginate(10)
        ->withQueryString();

    $generos = Libro::select('genero')->whereNotNull('genero')->distinct()->orderBy('genero')->pluck('genero');
    $tipos   = Libro::select('Tipo')->whereNotNull('Tipo')->distinct()->orderBy('Tipo')->pluck('Tipo');

    return view('welcome', compact('libros', 'generos', 'tipos'));
})->name('libros.index');

Route::get('/libros/crear', function () {
    return view('libros.create', [
        'autores'  => Autor::orderBy('Nombre')->get(),
        'editores' => Editor::orderBy('nombre_editorial')->get(),
    ]);
})->name('libros.create');

Route::post('/libros', function (Request $request) {
    $datos = $request->validate([
        'Titulo'    => 'required|string|max:255',
        'genero'    => 'nullable|string|max:100',
        'Tipo'      => 'nullable|string|max:100',
        'ID_autor'  => 'nullable|integer',
        'ID_editor' => 'nullable|integer',
        'imagen'    => 'nullable|image|max:2048',
    ]);

    if ($request->hasFile('imagen')) {
        $datos['imagen'] = $request->file('imagen')->store('libros', 'public');
    }

    Libro::create($datos);

    return redirect()->route('libros.index')->with('ok', 'Libro agregado');
})->name('libros.store');

Route::get('/libros/{libro}/editar', function (Libro $libro) {
    return view('libros.edit', [
        'libro'    => $libro,
        'autores'  => Autor::orderBy('Nombre')->get(),
        'editores' => Editor::orderBy('Nombre')->get(),
    ]);
})->name('libros.edit');

Route::put('/libros/{libro}', function (Request $request, Libro $libro) {
    $datos = $request->validate([
        'Titulo'    => 'required|string|max:255|unique:libros,Titulo,'.$libro->ID_libro.',ID_libro',
        'genero'    => 'nullable|string|max:100',
        'Tipo'      => 'nullable|string|max:100',
        'ID_autor'  => 'nullable|integer',
        'ID_editor' => 'nullable|integer',
        'imagen'    => 'nullable|image|max:2048',
    ]);

    if ($request->hasFile('imagen')) {
        $datos['imagen'] = $request->file('imagen')->store('libros', 'public');
    }

    $libro->update($datos);

    return redirect()->route('libros.index')->with('ok', 'Libro actualizado');
})->name('libros.update');

Route::delete('/libros/{libro}', function (Libro $libro) {
    $libro->delete();
    return redirect()->route('libros.index')->with('ok', 'Libro eliminado');
})->name('libros.destroy');
