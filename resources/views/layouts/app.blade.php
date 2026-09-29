<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Biblioteca Virtual')</title>
<style>
:root{--tinta:#14313a;--papel:#f4f6f5;--linea:#cdd8d6;--acento:#b4552d;--suave:#5b7077}
*{box-sizing:border-box}
body{margin:0;background:var(--papel);color:var(--tinta);font:16px/1.55 system-ui,sans-serif}
h1,h3{font-family:Georgia,serif;margin:0 0 .5rem}
a{color:inherit}
.wrap{max-width:1040px;margin:0 auto;padding:0 1.25rem}
header{background:var(--tinta);color:#fff;padding:1rem 0}
header .wrap{display:flex;justify-content:space-between;align-items:center}
header a{text-decoration:none}
.btn{display:inline-block;border:1px solid var(--tinta);background:var(--tinta);color:#fff;padding:.5rem 1rem;border-radius:4px;text-decoration:none;cursor:pointer;font:inherit}
.btn.sec{background:transparent;color:var(--tinta)}
.btn.peligro,header .btn{background:var(--acento);border-color:var(--acento)}
main{padding:2rem 0}
.filtros{display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.5rem}
input,select{font:inherit;padding:.5rem;border:1px solid var(--linea);border-radius:4px;max-width:100%}
.lista{list-style:none;margin:0;padding:0;border-top:1px solid var(--linea)}
.lista li{display:flex;justify-content:space-between;gap:1rem;padding:1rem 0;border-bottom:1px solid var(--linea)}
.meta{color:var(--suave);font-size:.9rem}
.etiqueta{border:1px solid var(--linea);border-radius:99px;padding:.1rem .7rem;font-size:.85rem;height:fit-content;background:#fff}
.ficha{background:#fff;border:1px solid var(--linea);padding:1.5rem;border-radius:4px}
.ficha input,.ficha select{width:100%}
label{display:block;font-weight:600;margin:1rem 0 .25rem}
dl{display:grid;grid-template-columns:9rem 1fr;gap:.6rem 1rem}
dt{color:var(--suave)} dd{margin:0}
.aviso{background:#e3eeeb;border-left:4px solid var(--tinta);padding:.7rem 1rem;margin-bottom:1rem}
.error{color:var(--acento);font-size:.9rem}
.acciones{display:flex;gap:.6rem;margin-top:1.25rem;flex-wrap:wrap}
nav svg{width:1rem;height:1rem}
</style>
</head>
<body>
<header><div class="wrap">
  <a href="{{ route('libros.index') }}"><strong>Biblioteca Virtual</strong></a>
  <a class="btn" href="{{ route('libros.create') }}">Agregar libro</a>
</div></header>
<main><div class="wrap">
  @if(session('ok'))<div class="aviso">{{ session('ok') }}</div>@endif
  @yield('content')
</div></main>
</body>
</html>
