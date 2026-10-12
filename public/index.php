<?php
$titulo = 'Inicio';
require __DIR__ . '/../includes/header.php';

$tablas = [
    'libros' => 'Libros', 'autores' => 'Autores', 'categorias' => 'Categorías',
    'usuarios' => 'Usuarios', 'prestamos' => 'Préstamos', 'ventas' => 'Ventas',
];
?>
<h2>Panel principal</h2>
<p>Sistema de gestión de la Biblioteca J&amp;M.</p>
<div class="tarjetas">
<?php foreach ($tablas as $tabla => $nombre): ?>
    <div class="tarjeta"><span><?= e(db()->query("SELECT COUNT(*) FROM $tabla")->fetchColumn()) ?></span><?= e($nombre) ?></div>
<?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
