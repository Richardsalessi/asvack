# 🛒 Proyecto Asvack

_Asvack es una plataforma de comercio electrónico tipo ecommerce desarrollada en Laravel, Blade, Vue.js y Tailwind, con base de datos MySQL. Está orientada a digitalizar procesos de venta física y llevarlos a un entorno online._

---

## 🚀 Requisitos

1. **PHP 8.2+**  
2. **XAMPP** (Apache + MySQL en puerto 3307)  
3. **Composer**  
   👉 [Descargar Composer](https://getcomposer.org/download/)  
4. **Node.js + npm**  
   👉 [Descargar Node.js](https://nodejs.org/en/download/)  
5. **Git LFS** (para archivos grandes)  
   👉 [Descargar Git LFS](https://git-lfs.com/)  
6. **7-Zip** (para descomprimir backups de la BD)  
   👉 [Descargar 7-Zip](https://www.7-zip.org/download.html)  

---

## ⚙️ Configuración de MySQL (XAMPP)

Editar el archivo:

C:/xampp/mysql/bin/my.ini


Agregar/ajustar:

```ini
[client]
port=3307
socket="C:/xampp/mysql/mysql.sock"
default-character-set=utf8mb4

[mysqld]
port=3307
socket="C:/xampp/mysql/mysql.sock"
basedir="C:/xampp/mysql"
datadir="C:/xampp/mysql/data"
tmpdir="C:/xampp/tmp"
pid_file="mysql.pid"
log_error="mysql_error.log"

# Charset
character-set-server=utf8mb4
collation-server=utf8mb4_general_ci

# Rendimiento y datos pesados
max_connections=150
max_allowed_packet=512M
net_read_timeout=120
net_write_timeout=120
tmp_table_size=256M
max_heap_table_size=256M
group_concat_max_len=1048576

# InnoDB
innodb_buffer_pool_size=1G
innodb_log_file_size=256M
innodb_log_buffer_size=64M
innodb_file_per_table=1
innodb_flush_log_at_trx_commit=1
innodb_lock_wait_timeout=50

✅ Esto asegura que MySQL soporte importación/exportación de datos pesados y consultas más grandes.

## 📂 Instalación del proyecto

### 1️⃣ Importar la base de datos
- Archivo: `asvack_db.sql`  
- Nombre en MySQL: `asvack_db`  
  👉 Usa **phpMyAdmin** o la línea de comandos para importarla.

---

### 2️⃣ Clonar el repositorio
```bash
git clone https://github.com/Richardsalessi/asvack.git
cd asvack
git lfs install


3️⃣ Instalar dependencias
composer install
npm install


4️⃣ Configurar el entorno
Copia el archivo de ejemplo:
cp .env.example .env
Edita el archivo .env con la siguiente configuración:

env
APP_NAME=Asvack
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=asvack_db
DB_USERNAME=root
DB_PASSWORD=


5️⃣ Generar la APP_KEY de Laravel
php artisan key:generate

6️⃣ Levantar los servidores
Compilar assets con Vite:
npm run dev

Iniciar servidor Laravel:
php artisan serve

7️⃣ Abrir en el navegador
👉 http://localhost:8000