@extends('layouts.app')
@section('content')
<h1>Catálogo</h1>
<form class="filtros" method="GET">
  <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por título">
  <select name="genero">
    <option value="">Todos los géneros</option>
    @foreach($generos as $g)<option value="{{ $g }}" @selected(request('genero')==$g)>{{ $g }}</option>@endforeach
  </select>
  <select name="tipo">
    <option value="">Todos los tipos</option>
    @foreach($tipos as $t)<option value="{{ $t }}" @selected(request('tipo')==$t)>{{ $t }}</option>@endforeach
  </select>
  <button class="btn">Buscar</button>
</form>

<ul class="lista">
@forelse($libros as $l)
  <li>
  <div>
    @if($l->imagen)
      <img src="{{ asset('storage/'.$l->imagen) }}" alt="{{ $l->Titulo }}" style="width:60px;height:80px;object-fit:cover;">
    @endif
    <h3><a href="{{ route('libros.show', $l) }}">{{ $l->Titulo }}</a></h3>
      <div class="meta">
        @if($l->autor){{ $l->autor->Nombre }} {{ $l->autor->Apellidos }}@else Autor desconocido @endif
        @if($l->editor), editorial {{ $l->editor->nombre_editorial }}@endif
      </div>
    </div>
    @if($l->genero)<span class="etiqueta">{{ $l->genero }}</span>@endif
  </li>
@empty
  <li>No hay libros con esos filtros. Prueba con otro título o agrega uno nuevo.</li>
@endforelse
</ul>
<nav>{{ $libros->links() }}</nav>
@endsection
