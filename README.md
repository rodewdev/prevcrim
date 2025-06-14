<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>
Instrucciones para ejecutar el sistema

Sigue estos pasos para ejecutar el proyecto Laravel:
1. Clonar el repositorio

git clone [<URL_DEL_REPOSITORIO>](https://github.com/rodewdev/prevcrim)
cd <CARPETA_DEL_PROYECTO>

2. Instalar dependencias de Node.js

npm install

3. Instalar dependencias de PHP

composer install

4. Configurar el archivo de entorno

Copia el archivo .env.example a .env:

cp .env.example .env

5. Generar la clave de la aplicación

php artisan key:generate

6. Ejecutar las migraciones y los seeds (si es necesario)

php artisan migrate --seed

7. Iniciar el servidor

php artisan serve

Accede al sistema en tu navegador en http://127.0.0.1:8000
