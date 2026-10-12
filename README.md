# Biblioteca J&M

Sistema de gestión de biblioteca (libros, autores, categorías, usuarios, préstamos y devoluciones).

## Tecnologías
- PHP 8.5 (PDO) · PostgreSQL 18 · Apache 2.4 · Ubuntu Server 26.04 LTS
- Firewall UFW (puertos 22, 80, 443 y 5432)

## Instalación
1. Instalar dependencias: `sudo apt install apache2 php libapache2-mod-php php-pgsql postgresql`
2. Importar la base de datos: `sudo -u postgres createdb biblioteca_jm` y luego `sudo -u postgres psql biblioteca_jm < sql/biblioteca_jm.sql`
3. Crear un usuario de BD con permisos sobre las tablas.
4. Copiar `config/config.example.php` a `config/config.php` y poner la contraseña.
5. Apuntar el DocumentRoot de Apache a la carpeta `public/`.

## Estructura
- `public/` páginas de la aplicación (único contenido expuesto por Apache)
- `includes/` conexión a la BD, plantilla y CRUD genérico
- `config/` configuración (el archivo real no se sube al repositorio)
- `sql/` script de la base de datos
