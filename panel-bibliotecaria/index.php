```php
<?php
require_once __DIR__ . '/config/conexion.php';

function contarRegistros(PDO $conexion, string $sql): int
{
    return (int) $conexion->query($sql)->fetchColumn();
}

$totalLibros = contarRegistros($conexion, 'SELECT COUNT(*) FROM libros');
$prestamosActivos = contarRegistros(
    $conexion,
    "SELECT COUNT(*) FROM prestamos WHERE estado = 'activo'"
);
$reservasPendientes = contarRegistros(
    $conexion,
    "SELECT COUNT(*) FROM reservas WHERE estado = 'pendiente'"
);
$multasPendientes = contarRegistros(
    $conexion,
    "SELECT COUNT(*) FROM multas WHERE estado = 'pendiente'"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BiblioTech | Panel de bibliotecaria</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f5f9;
            color: #1e293b;
        }
        .layout { display: flex; min-height: 100vh; }
        aside {
            width: 245px;
            flex-shrink: 0;
            background: #172554;
            color: white;
            padding: 28px 18px;
        }
        .brand { font-size: 25px; font-weight: bold; margin-bottom: 8px; }
        .subtitle { color: #cbd5e1; font-size: 13px; margin-bottom: 32px; }
        nav a {
            display: block;
            color: #e2e8f0;
            text-decoration: none;
            padding: 13px 14px;
            margin-bottom: 7px;
            border-radius: 8px;
        }
        nav a:hover, nav a.active { background: #1d4ed8; color: white; }
        main { flex: 1; padding: 34px; min-width: 0; }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        h1 { margin: 0 0 8px; font-size: 28px; }
        .muted { color: #64748b; }
        .role {
            background: white;
            border: 1px solid #e2e8f0;
            padding: 10px 14px;
            border-radius: 20px;
            font-size: 13px;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }
        .card, .welcome {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 23px;
            box-shadow: 0 3px 10px rgba(15, 23, 42, .04);
        }
        .card .label { color: #64748b; font-size: 14px; }
        .card .number { font-size: 32px; font-weight: bold; margin: 13px 0; }
        .card a { color: #1d4ed8; text-decoration: none; font-size: 14px; }
        .welcome h2 { margin-top: 0; }
        .welcome p { color: #64748b; line-height: 1.7; }
        .quick-links { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 20px; }
        .button {
            display: inline-block;
            padding: 12px 16px;
            background: #1d4ed8;
            color: white;
            border-radius: 8px;
            text-decoration: none;
        }
        .button.secondary { background: #e2e8f0; color: #172554; }
        footer { margin-top: 30px; color: #94a3b8; font-size: 12px; }
        @media (max-width: 1000px) {
            .cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 650px) {
            .layout { display: block; }
            aside { width: 100%; }
            main { padding: 20px; }
            .cards { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="layout">
    <aside>
        <div class="brand">📚 BiblioTech</div>
        <div class="subtitle">Sistema de biblioteca</div>

        <nav>
            <a class="active" href="index.php">▦ &nbsp; Panel principal</a>
            <a href="libros.php">▤ &nbsp; Consultar libros</a>
            <a href="reservas.php">▣ &nbsp; Reservas</a>
            <a href="prestamos.php">⇄ &nbsp; Préstamos y devoluciones</a>
            <a href="multas.php">S/ &nbsp; Multas</a>
        </nav>
    </aside>

    <main>
        <div class="topbar">
            <div>
                <h1>Panel de bibliotecaria</h1>
                <div class="muted">Resumen general de la biblioteca</div>
            </div>
            <div class="role">Personal bibliotecario</div>
        </div>

        <section class="cards">
            <div class="card">
                <div class="label">Libros registrados</div>
                <div class="number"><?= number_format($totalLibros) ?></div>
                <a href="libros.php">Consultar catálogo →</a>
            </div>

            <div class="card">
                <div class="label">Préstamos activos</div>
                <div class="number"><?= number_format($prestamosActivos) ?></div>
                <a href="prestamos.php">Gestionar devoluciones →</a>
            </div>

            <div class="card">
                <div class="label">Reservas pendientes</div>
                <div class="number"><?= number_format($reservasPendientes) ?></div>
                <a href="reservas.php">Consultar reservas →</a>
            </div>

            <div class="card">
                <div class="label">Multas pendientes</div>
                <div class="number"><?= number_format($multasPendientes) ?></div>
                <a href="multas.php">Consultar multas →</a>
            </div>
        </section>

        <section class="welcome">
            <h2>Bienvenida al sistema</h2>
            <p>
                Desde este panel puedes consultar el catálogo de libros,
                revisar las reservas, registrar préstamos, procesar devoluciones
                y consultar las multas de los usuarios.
            </p>
            <div class="quick-links">
                <a class="button" href="libros.php">Consultar libros</a>
                <a class="button secondary" href="prestamos.php">Préstamos y devoluciones</a>
            </div>
        </section>

        <footer>BiblioTech · Panel de gestión bibliotecaria</footer>
    </main>
</div>
</body>
</html>
```
