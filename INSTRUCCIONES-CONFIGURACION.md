# 🏨 Hotel Rocamonti - Instrucciones de Configuración

## ✅ Problemas Corregidos

He corregido los siguientes problemas en tu sistema:

1. **✅ Base de datos configurada**: Ahora el sistema usa MySQL con la base de datos `rocamonti`
2. **✅ API corregida**: El frontend ahora se conecta correctamente al backend Laravel en `http://localhost:8000/api`
3. **✅ Rutas corregidas**: Las llamadas a la API ahora usan las rutas correctas de Laravel
4. **✅ Archivo .env creado**: Configuración completa para el backend

## 📋 Pasos para Iniciar el Sistema

### Paso 1: Verificar que XAMPP está ejecutándose

1. Abre el panel de control de XAMPP
2. Inicia **Apache**
3. Inicia **MySQL**
4. Verifica que ambos servicios estén corriendo (luz verde)

### Paso 2: Crear la base de datos

1. Abre el navegador y ve a: `http://localhost/phpmyadmin`
2. Haz clic en "Nueva" en el panel izquierdo
3. Crea una base de datos llamada: **`rocamonti`**
4. Selecciona cotejamiento: `utf8mb4_unicode_ci`
5. Haz clic en "Crear"

### Paso 3: Configurar el sistema

1. Navega a la carpeta del proyecto:
   ```
   C:\xampp\htdocs\hotel_rocamonti-main\hotel_rocamonti-main
   ```

2. Ejecuta el archivo: **`CONFIGURAR-SISTEMA.bat`**
   - Este script automáticamente:
     - Genera la clave de seguridad de Laravel
     - Limpia cachés
     - Crea las tablas en la base de datos
     - Llena la base de datos con datos de ejemplo
     - Instala dependencias del frontend

3. Espera a que termine (puede tardar 1-2 minutos)

### Paso 4: Iniciar el sistema

1. Ejecuta el archivo: **`INICIAR-SISTEMA.bat`**
   - Este abrirá 2 ventanas de terminal:
     - Backend Laravel (puerto 8000)
     - Frontend Vue (puerto 5173)

2. Espera a que ambos servidores estén listos

3. Abre tu navegador y ve a: **`http://localhost:5173`**

## 🎯 URLs del Sistema

- **Frontend (Página pública)**: http://localhost:5173
- **Backend API**: http://localhost:8000/api
- **Admin Laravel**: http://localhost:8000

## 🔧 Configuración de Base de Datos

El archivo `.env` ya está configurado con:
- **DB_CONNECTION**: mysql
- **DB_HOST**: 127.0.0.1
- **DB_PORT**: 3306
- **DB_DATABASE**: rocamonti
- **DB_USERNAME**: root
- **DB_PASSWORD**: (vacío - por defecto en XAMPP)

## ❗ Solución de Problemas

### Si las habitaciones no cargan:

1. **Verifica la consola del navegador** (F12 → Console)
   - Busca errores en rojo
   - Verifica que las peticiones lleguen a `http://localhost:8000/api/room-types`

2. **Verifica que el backend esté corriendo**:
   - Abre: http://localhost:8000/api/test
   - Deberías ver un JSON con mensaje "Hotel Rocamonti API funcionando"

3. **Verifica la base de datos**:
   - Abre phpMyAdmin: http://localhost/phpmyadmin
   - Selecciona la base de datos `rocamonti`
   - Verifica que existen las tablas: `tipo_habitacions`, `habitacions`, etc.
   - Verifica que la tabla `tipo_habitacions` tenga datos

4. **Verifica los logs de Laravel**:
   ```
   backend\storage\logs\laravel.log
   ```

### Si hay error de conexión a base de datos:

1. Verifica que MySQL esté corriendo en XAMPP
2. Verifica que la base de datos `rocamonti` exista
3. Verifica las credenciales en `backend\.env`:
   ```
   DB_CONNECTION=mysql
   DB_DATABASE=rocamonti
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### Si el frontend no carga:

1. Verifica que npm esté instalado: `npm --version`
2. Instala dependencias: `cd frontend && npm install`
3. Inicia el servidor: `npm run dev`

## 📁 Archivos Modificados

He modificado los siguientes archivos:

1. **`backend\.env`** (NUEVO)
   - Configuración de base de datos MySQL
   - Configuración de la aplicación

2. **`frontend\src\services\api.js`**
   - Cambió de usar PHP emergencia a Laravel API
   - URL base: `http://localhost:8000/api`

3. **`frontend\src\views\ReservarPage.vue`**
   - Corregidas las rutas de API
   - Cambió de `''` a `/room-types`
   - Cambió `/hotel-api-emergencia.php` a `/rooms/${tipo.id}/occupied-dates`

## 🎨 Estructura de la Base de Datos

Las siguientes tablas se crearán automáticamente:

- **users**: Usuarios del sistema
- **tipo_habitacions**: Tipos de habitaciones (Estándar, Suite, etc.)
- **habitacions**: Habitaciones individuales
- **clientes**: Datos de clientes
- **reservas**: Reservas realizadas
- **pagos**: Pagos de las reservas

## 🚀 Datos de Prueba

El sistema incluye datos de prueba (seeders):

- 4 tipos de habitaciones
- 20+ habitaciones
- Usuario admin (si está configurado)

## 📞 Soporte

Si tienes problemas:

1. Revisa los logs en `backend\storage\logs\laravel.log`
2. Revisa la consola del navegador (F12)
3. Verifica que XAMPP esté corriendo
4. Verifica que la base de datos exista

---

**¡Tu sistema está listo para funcionar!** 🎉
