@if (session('ok'))
    <p class="aviso">{{ session('ok') }}</p>
@endif

<div class="estante">
    @forelse ($libros as $libro)
        <article class="libro">
            <div class="portada">
                @if ($libro->imagen)
                    <img src="{{ asset('storage/' . $libro->imagen) }}" alt="{{ $libro->Titulo }}">
                @else
                    <span>{{ $libro->Titulo }}</span>
                @endif
            </div>
            <div class="tabla-madera"></div>

            <h3>{{ $libro->Titulo }}</h3>
            <p class="meta">{{ $libro->autor->nombre ?? 'Autor desconocido' }}, {{ $libro->editor->nombre ?? 'Sin editorial' }}</p>
            <span class="etiqueta">{{ $libro->genero }}</span>

            <div class="acciones">
                <a href="{{ route('libros.edit', $libro) }}" class="btn btn-editar">Editar</a>

                <form action="{{ route('libros.destroy', $libro) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar este libro? No se puede deshacer.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-eliminar">Eliminar</button>
                </form>
            </div>
        </article>
    @empty
        <p>No hay libros. Agrega el primero.</p>
    @endforelse
</div>
