<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/crud_simple.php';

crud_simple([
    'tabla'  => 'autores',
    'id'     => 'id_autor',
    'campo'  => 'nombre',
    'plural' => 'Autores',
    'pagina' => 'autores.php',
]);
