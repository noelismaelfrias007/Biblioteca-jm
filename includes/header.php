<?php require_once __DIR__ . '/db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'Biblioteca J&M') ?> - Biblioteca J&amp;M</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<header>
    <h1>📚 Biblioteca J&amp;M</h1>
    <nav>
        <a href="index.php">Inicio</a>
        <a href="libros.php">Libros</a>
        <a href="autores.php">Autores</a>
        <a href="categorias.php">Categorías</a>
        <a href="usuarios.php">Usuarios</a>
        <a href="prestamos.php">Préstamos</a>
    </nav>
</header>
<main>
<?php if (!empty($_GET['ok'])): ?><div class="alerta ok"><?= e($_GET['ok']) ?></div><?php endif; ?>
<?php if (!empty($_GET['error'])): ?><div class="alerta error"><?= e($_GET['error']) ?></div><?php endif; ?>
