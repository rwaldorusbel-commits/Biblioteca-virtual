```php
<?php
require_once __DIR__ . '/config/conexion.php';

$busqueda = trim($_GET['buscar'] ?? '');

$sql = "
    SELECT
        l.id,
        l.titulo,
        l.autor,
        l.isbn,
        l.editorial,
        l.anio,
        l.stock_total,
        l.stock_disponible,
        c.nombre AS categoria
    FROM libros l
    LEFT JOIN categorias c ON c.id = l.id_categoria
";

if ($busqueda !== '') {
    $sql .= "
        WHERE l.titulo LIKE :titulo
           OR l.autor LIKE :autor
           OR l.isbn LIKE :isbn
    ";
}

$sql .= " ORDER BY l.titulo ASC";

$stmt = $conexion->prepare($sql);

if ($busqueda !== '') {
    $termino = '%' . $busqueda . '%';
    $stmt->execute([
        'titulo' => $termino,
        'autor' => $termino,
        'isbn' => $termino,
    ]);
} else {
    $stmt->execute();
}

$libros = $stmt->fetchAll();

function h($valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consultar libros | BiblioTech</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f5f9; color: #1e293b; }
        header { background: #172554; color: white; padding: 22px 32px; }
        header a { color: #bfdbfe; text-decoration: none; }
        main { max-width: 1250px; margin: 30px auto; padding: 0 22px; }
        .panel { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; }
        .muted { color: #64748b; }
        .search { display: flex; gap: 10px; margin: 20px 0; }
        input { flex: 1; min-width: 0; padding: 12px; border: 1px solid #cbd5e1; border-radius: 7px; font-size: 15px; }
        button, .clear { padding: 12px 17px; border: 0; border-radius: 7px; background: #1d4ed8; color: white; text-decoration: none; cursor: pointer; }
        .clear { background: #e2e8f0; color: #172554; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        th, td { padding: 13px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background: #f8fafc; color: #475569; font-size: 13px; }
        .stock { display: inline-block; padding: 5px 9px; border-radius: 15px; background: #dcfce7; color: #166534; }
        .empty { padding: 25px; text-align: center; color: #64748b; }
        @media(max-width: 600px) { header { padding: 20px; } .search { flex-wrap: wrap; } }
    </style>
</head>
<body>
<header>
    <h1>📚 BiblioTech — Consultar libros</h1>
    <a href="index.php">← Volver al panel principal</a>
</header>

<main>
    <section class="panel">
        <h2>Catálogo bibliográfico</h2>
        <p class="muted">Consulta libros por título, autor o ISBN. Esta pantalla no permite modificar ni eliminar registros.</p>

        <form class="search" method="get" action="libros.php">
            <input
                type="search"
                name="buscar"
                placeholder="Escribe el título, autor o ISBN..."
                value="<?= h($busqueda) ?>"
            >
            <button type="submit">Buscar</button>
            <a class="clear" href="libros.php">Limpiar</a>
        </form>

        <p class="muted">
            Resultados encontrados: <strong><?= count($libros) ?></strong>
        </p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>ISBN</th>
                        <th>Categoría</th>
                        <th>Editorial</th>
                        <th>Año</th>
                        <th>Stock total</th>
                        <th>Disponible</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$libros): ?>
                    <tr>
                        <td colspan="9" class="empty">No se encontraron libros.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($libros as $libro): ?>
                        <tr>
                            <td><?= h($libro['id']) ?></td>
                            <td><?= h($libro['titulo']) ?></td>
                            <td><?= h($libro['autor']) ?></td>
                            <td><?= h($libro['isbn'] ?: '—') ?></td>
                            <td><?= h($libro['categoria'] ?: 'Sin categoría') ?></td>
                            <td><?= h($libro['editorial'] ?: '—') ?></td>
                            <td><?= h($libro['anio'] ?: '—') ?></td>
                            <td><?= h($libro['stock_total']) ?></td>
                            <td><span class="stock"><?= h($libro['stock_disponible']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
```
