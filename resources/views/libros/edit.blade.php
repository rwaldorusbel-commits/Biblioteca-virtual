<form action="{{ route('libros.update', $libro) }}" method="POST" enctype="multipart/form-data" class="formulario">
    @csrf
    @method('PUT')

    <h2>Editar libro</h2>

    <label>Título
        <input name="Titulo" value="{{ old('Titulo', $libro->Titulo) }}" required>
    </label>

    <label>Género
        <select name="genero">
            @foreach (['Romance','Aventura','Misterio','Fantasía','Historia'] as $g)
                <option @selected(old('genero', $libro->genero) === $g)>{{ $g }}</option>
            @endforeach
        </select>
    </label>

    <label>Tipo
        <select name="Tipo">
            @foreach (['Novela','Cuento','Ensayo','Poesía'] as $t)
                <option @selected(old('Tipo', $libro->Tipo) === $t)>{{ $t }}</option>
            @endforeach
        </select>
    </label>

    <label>Autor
        <select name="ID_autor">
            <option value="">Sin autor</option>
            @foreach ($autores as $autor)
                <option value="{{ $autor->getKey() }}" @selected(old('ID_autor', $libro->ID_autor) == $autor->getKey())>
                    {{ $autor->nombre }}
                </option>
            @endforeach
        </select>
    </label>

    <label>Editorial
        <select name="ID_editor">
            <option value="">Sin editorial</option>
            @foreach ($editores as $editor)
                <option value="{{ $editor->getKey() }}" @selected(old('ID_editor', $libro->ID_editor) == $editor->getKey())>
                    {{ $editor->nombre }}
                </option>
            @endforeach
        </select>
    </label>

    <label>Portada (opcional, deja vacío para conservar la actual)
        <input type="file" name="imagen" accept="image/*">
    </label>

    @if ($errors->any())
        <p class="aviso">{{ $errors->first() }}</p>
    @endif

    <a href="{{ route('libros.index') }}" class="btn">Cancelar</a>
    <button class="btn btn-editar">Guardar cambios</button>
</form>
