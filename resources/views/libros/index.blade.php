<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Biblioteca Virtual</title>
    <style>
        body{margin:0;background:#f4f5f5;color:#13303a;font:16px/1.5 system-ui,sans-serif}
        header{background:#13303a;color:#fff}
        .wrap{max-width:1040px;margin:0 auto;padding:0 20px}
        header .wrap{display:flex;justify-content:space-between;align-items:center;height:72px}
        h2{font-family:Georgia,serif;font-size:2rem;margin:32px 0 0}
        .estante{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:26px 22px;padding:26px 0}
        .libro{display:flex;flex-direction:column}
        .portada{aspect-ratio:2/3;position:relative;background:#1f4e5f;color:#fff;border-radius:3px 8px 8px 3px;
            box-shadow:inset 6px 0 0 rgba(0,0,0,.22),0 6px 10px rgba(0,0,0,.25);
            display:flex;align-items:flex-end;padding:14px 12px 14px 20px;
            font-family:Georgia,serif;font-weight:700;overflow:hidden}
        .portada img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
        .tabla-madera{height:12px;background:#8a6a4a;border-radius:2px;margin:-2px -8px 0;box-shadow:0 5px 8px rgba(0,0,0,.25)}
        .libro h3{font-family:Georgia,serif;font-size:1.05rem;margin:14px 0 2px}
        .meta{color:#5b6b70;font-size:.85rem;margin:0}
        .etiqueta{align-self:flex-start;margin-top:8px;border:1px solid #d8dddf;border-radius:999px;padding:1px 10px;font-size:.78rem;background:#fff}
        .acciones{display:flex;gap:8px;margin-top:12px}
        .acciones form{flex:1;margin:0}
        .btn{display:inline-block;width:100%;box-sizing:border-box;text-align:center;padding:6px 8px;font:inherit;font-size:.85rem;
            border-radius:6px;border:1px solid #d8dddf;background:transparent;color:#13303a;cursor:pointer;text-decoration:none}
        .acciones > a.btn{flex:1}
        .btn-editar{background:#13303a;color:#fff;border-color:#13303a}
        .btn-eliminar{color:#a32a2a;border-color:#a32a2a}
        .btn-eliminar:hover{background:#a32a2a;color:#fff}
        .btn-agregar{background:#b5532c;color:#fff;border-color:#b5532c;width:auto;padding:9px 16px;font-size:1rem}
        .aviso{background:#e8f1ee;border:1px solid #b9d3cb;padding:10px 14px;border-radius:6px;margin-top:16px}
    </style>
</head>
<body>
    <header>
        <div class="wrap">
            <strong>Biblioteca Virtual</strong>
            <a href="{{ route('libros.create') }}" class="btn btn-agregar">Agregar libro</a>
        </div>
    </header>

    <main class="wrap">
        <h2>Catálogo</h2>

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
    </main>
</body>
</html>
