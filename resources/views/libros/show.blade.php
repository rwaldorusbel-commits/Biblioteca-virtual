@extends('layouts.app')
@section('content')
<h1>{{ $libro->Titulo }}</h1>
@if($libro->imagen)
  <img src="{{ asset('storage/'.$libro->imagen) }}" alt="{{ $libro->Titulo }}" style="max-width:200px;">
@endif
<p>Autor: {{ $libro->autor ? $libro->autor->Nombre.' '.$libro->autor->Apellidos : 'Autor desconocido' }}</p>
<p>Editorial: {{ $libro->editor->nombre_editorial ?? 'Sin editorial' }}</p>
<p>Género: {{ $libro->genero }}</p>
<p>Tipo: {{ $libro->Tipo }}</p>
<a href="/">Volver al catálogo</a>
@endsection
