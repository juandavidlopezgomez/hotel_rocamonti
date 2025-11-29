# 🚀 Guía para Iniciar el Servidor Backend

## ✅ Solución Aplicada

Se ha corregido el problema de CORS que impedía que las habitaciones se cargaran desde el frontend.

### Cambios Realizados:

1. ✅ **Middleware CORS actualizado** (`app/Http/Middleware/ForceJsonResponse.php`)
   - Ahora maneja correctamente peticiones OPTIONS (preflight)
   - Permite conexiones desde cualquier puerto de localhost
   - Headers CORS configurados correctamente

2. ✅ **Configuración CORS** (`config/cors.php`)
   - Orígenes permitidos: `*`
   - Credenciales habilitadas

3. ✅ **Scripts de inicio mejorados**
   - `REINICIAR-SERVIDOR.bat`: Reinicia el servidor limpiando caché
   - `INICIAR-SERVIDOR.bat`: Inicia el servidor normalmente

---

## 🎯 PASOS PARA INICIAR EL SERVIDOR

### ⚠️ IMPORTANTE: Cierra el servidor anterior

Si tienes un servidor corriendo, **ciérralo presionando `Ctrl+C`** en la ventana del CMD/PowerShell.

### Opción 1: Usar el Script de Reinicio (RECOMENDADO)

1. Navega a la carpeta del proyecto:
   ```
   C:\xampp\htdocs\hotel-rocamonti\
   ```

2. Haz **doble clic** en:
   ```
   REINICIAR-SERVIDOR.bat
   ```

Este script:
- ✓ Detiene servidores antiguos
- ✓ Limpia caché de Laravel
- ✓ Recarga configuración
- ✓ Inicia el servidor con CORS habilitado

### Opción 2: Manual desde PowerShell

```powershell
cd C:\xampp\htdocs\hotel-rocamonti\backend

# Limpiar caché
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Iniciar servidor
php artisan serve --host=0.0.0.0 --port=8000
```

---

## 🔍 Verificar que Funciona

Una vez iniciado el servidor, verás:

```
INFO  Server running on [http://127.0.0.1:8000].
```

### Probar el API:

1. **Desde el navegador**, abre:
   ```
   http://localhost:8000/api/test
   ```
   Deberías ver un JSON con información del hotel.

2. **Desde tu aplicación frontend**, recarga la página.
   Las habitaciones deberían cargar sin errores CORS.

---

## 🐛 Solución de Problemas

### Error: "No 'Access-Control-Allow-Origin' header"

**Causa**: El servidor aún tiene el código antiguo en caché.

**Solución**:
1. Detén el servidor (`Ctrl+C`)
2. Ejecuta `REINICIAR-SERVIDOR.bat`

### Error: "Port 8000 already in use"

**Causa**: Ya hay un servidor corriendo en el puerto 8000.

**Solución**:
1. Busca la ventana de CMD/PowerShell con el servidor
2. Presiona `Ctrl+C` para detenerlo
3. O ejecuta `REINICIAR-SERVIDOR.bat` que lo hace automáticamente

### El servidor se detiene al cargar habitaciones

**Causa**: Error en el código PHP.

**Solución**:
1. Revisa el archivo de logs: `backend/storage/logs/laravel.log`
2. Busca la última línea con `ERROR` o `Exception`
3. Reporta el error completo

---

## 📝 Endpoints Disponibles

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/api/test` | GET | Verifica que el API funciona |
| `/api/room-types` | GET | Lista todos los tipos de habitación |
| `/api/room-types?num_adultos=2` | GET | Filtra por número de adultos |
| `/api/rooms/{id}` | GET | Obtiene detalles de una habitación |
| `/api/rooms/{id}/occupied-dates` | GET | Obtiene fechas ocupadas |

---

## 🔧 Configuración Técnica

### CORS Headers Configurados:

```
Access-Control-Allow-Origin: http://localhost:5173 (o el puerto de tu frontend)
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH
Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With, Origin
Access-Control-Allow-Credentials: true
Access-Control-Max-Age: 86400
```

### Middleware Activo:

- `ForceJsonResponse`: Fuerza respuestas JSON y configura CORS
- Ubicación: `app/Http/Middleware/ForceJsonResponse.php`

---

## ✨ Estado del Servidor

Cuando el servidor está corriendo correctamente:

✅ Puerto 8000 escuchando
✅ CORS configurado para localhost
✅ Middleware activo
✅ Base de datos conectada
✅ 9 tipos de habitaciones disponibles

---

## 📞 Soporte

Si sigues teniendo problemas:

1. Verifica que MySQL de XAMPP esté corriendo
2. Revisa los logs en `backend/storage/logs/laravel.log`
3. Asegúrate de tener PHP 8.2+ instalado
4. Confirma que el puerto 8000 no está siendo usado por otra aplicación
