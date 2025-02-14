Documentación API TSG
1. Instalación y Configuración del Proyecto
1.1 Requisitos Previos

PHP 8.1 o superior
Composer
MySQL (o SQLite – ver Nota en sección 4)

Laravel 11
Extensiones PHP: pdo, pdo_mysql, mbstring, tokenizer, xml, ctype, json, openssl, gd


1.2 Clonar el Repositorio

git clone https://github.com/costalautaro/prueba-tecnica-tsg


1.3 Instalación de Dependencias

composer install

1.4 Configuración del Entorno

Copiar el archivo .env.example a .env

Configurar la conexión a la base de datos:


DB_CONNECTION=mysql  
DB_HOST=127.0.0.1  
DB_PORT=3306  
DB_DATABASE=user_post_hub  
DB_USERNAME=root  
DB_PASSWORD=root


en config/database.php 
se puede cambiar el default para usar sqlite


IMPORTANTE:
Hay que crear previamente la base de datos (con el nombre definido en DB_DATABASE, por ejemplo, user_post_hub) en tu gestor de bases de datos antes de ejecutar las migraciones.


1.5 Generar la Clave de la Aplicación


php artisan key:generate


1.6 Migraciones y Seeders


php artisan migrate --seed

2. Documentación de la API

2.1 Documentación con Swagger

Iniciar el servidor:

php artisan serve

Acceder a la documentación en:

http://127.0.0.1:8000/api/documentation

2.2 Uso de la Colección de Postman

en la raiz esta el archivo
API Prueba Técnica - Laravel 11.postman_collection.json que podes usar para probar además de swagger


Abrir Postman-> Configure la variable base_url con el valor: http://127.0.0.1:8000 

3. Seeders y Pruebas Unitarias

3.1 Poblar la Base de Datos

php artisan db:seed






3.2 Ejecución de Pruebas Unitarias (no estaba en el enunciado pero asumí que eran importantes)



php artisan test

4. Consideraciones y Notas Adicionales
Nota: En el archivo database.php se encuentra comentada la línea para poder usar SQLite. Se dejó de esa forma por si les resulta más práctico.

Validaciones Adicionales:
Dado que la API no implementa roles, solo se permite que el usuario autenticado que creó un post pueda eliminarlo.
La misma lógica se aplica para la eliminación de usuarios: únicamente el usuario autenticado podrá borrar su propia cuenta.
Observaciones / TODO:
Hubiera sido ideal implementar pruebas unitarias y mejorar las pruebas de funcionalidad (features).
Se sugiere estandarizar los mensajes errores que provienen de JWT a español para mayor consistencia con algun archivo de traduccion.
