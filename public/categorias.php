<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/crud_simple.php';

crud_simple([
    'tabla'  => 'categorias',
    'id'     => 'id_categoria',
    'campo'  => 'nombre_categoria',
    'plural' => 'Categorías',
    'pagina' => 'categorias.php',
]);
