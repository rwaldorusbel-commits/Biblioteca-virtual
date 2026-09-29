<p><input name="Titulo" value="{{ old('Titulo', $libro->Titulo ?? '') }}" placeholder="Título" required></p>
<p><input name="genero" value="{{ old('genero', $libro->genero ?? '') }}" placeholder="Género"></p>
<p><input name="Tipo" value="{{ old('Tipo', $libro->Tipo ?? '') }}" placeholder="Tipo"></p>
<p><input type="file" name="imagen" accept="image/*"></p>
<p>

  <select name="ID_autor">
    <option value="">Autor</option>
    @foreach($autores as $a)
      <option value="{{ $a->ID_autores }}" @selected(old('ID_autor', $libro->ID_autor ?? '') == $a->ID_autores)>
        {{ $a->Nombre }} {{ $a->Apellidos }}
      </option>
    @endforeach
  </select>
</p>
<p>
  <select name="ID_editor">
    <option value="">Editorial</option>
    @foreach($editores as $e)
      <option value="{{ $e->ID_editores }}" @selected(old('ID_editor', $libro->ID_editor ?? '') == $e->ID_editores)>
        {{ $e->Nombre }}
      </option>
    @endforeach
  </select>
</p>
<button class="btn">Guardar</button>
