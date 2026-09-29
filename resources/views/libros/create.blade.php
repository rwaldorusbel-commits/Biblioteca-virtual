@extends('layouts.app')
@section('content')
<h1>Agregar libro</h1>

@if($errors->any())
  <div class="aviso">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('libros.store') }}" enctype="multipart/form-data">
  @csrf
  @include('libros.form')
</form>
@endsection
