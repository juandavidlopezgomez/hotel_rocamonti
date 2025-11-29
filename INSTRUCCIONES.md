# Hotel Rocamonti - Sistema de Reservas

## Instrucciones de Instalación y Configuración

### BACKEND (Laravel)

#### 1. Instalar dependencias de Composer
```bash
cd backend
composer install
composer require guzzlehttp/guzzle
```

#### 2. Verificar configuración de base de datos
El archivo `.env` ya está configurado con:
- Base de datos: `rocamonti`
- Usuario: `root`
- Contraseña: (vacío - configuración por defecto de XAMPP)

**IMPORTANTE:** Asegúrate de crear la base de datos `rocamonti` en MySQL antes de ejecutar las migraciones.

```sql
CREATE DATABASE rocamonti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 3. Ejecutar migraciones y seeders
```bash
php artisan migrate:fresh --seed
```

Esto creará:
- 9 tipos de habitaciones con precios en COP
- 27 habitaciones (3 por cada tipo)
- Todas las tablas necesarias del sistema

#### 4. Iniciar servidor de desarrollo
```bash
php artisan serve
```

El backend estará disponible en: `http://localhost:8000`

#### 5. Probar la API
```bash
# Test básico
curl http://localhost:8000/api/test

# Ver tipos de habitación
curl http://localhost:8000/api/room-types

# Ver habitaciones disponibles
curl http://localhost:8000/api/rooms
```

### FRONTEND (Vue.js)

#### 1. Instalar dependencias de npm
```bash
cd frontend
npm install
```

Esto instalará todas las dependencias incluyendo:
- Vue 3
- Vue Router
- Pinia (state management)
- Axios
- Dayjs
- Vue Datepicker

#### 2. Iniciar servidor de desarrollo
```bash
npm run dev
```

El frontend estará disponible en: `http://localhost:5173`

### CONFIGURACIÓN DE WOMPI

Las credenciales de Wompi ya están configuradas en el `.env` del backend:
- Public Key: `pub_prod_GMN9W6Y3sXbp7uE5RXo4cDmPUzkR89y5`
- Private Key: `prv_prod_S4urLM3mXXgvwPRBdTUfZltyVSwReB8y`
- API URL: `https://production.wompi.co/v1`

**NOTA:** Estas son credenciales de producción. Asegúrate de que sean correctas y tengas autorización para usarlas.

### FLUJO DE LA APLICACIÓN

#### Paso 1: Página de inicio
- Visita `http://localhost:5173`
- Verás una página full-screen con imagen de fondo
- Haz clic en "Reservar Ahora"

#### Paso 2: Proceso de reserva (6 pasos)
1. **Selección de fechas**: Elige fecha entrada, salida y número de personas
2. **Habitaciones disponibles**: Ve los tipos de habitación disponibles con precios
3. **Habitación específica**: Elige una habitación específica del tipo seleccionado
4. **Datos del cliente**: Completa formulario con tus datos
5. **Pago**: Resumen de reserva y botón para pagar con Wompi
6. **Confirmación**: Página con detalles de la reserva confirmada

### ESTRUCTURA DEL PROYECTO

#### Backend (Laravel)
```
backend/
├── app/
│   ├── Models/              # TipoDeHabitacion, Habitacion, Cliente, Reserva, etc.
│   └── Http/Controllers/    # RoomController, BookingController, PaymentController
├── database/
│   ├── migrations/          # 7 migraciones para todas las tablas
│   └── seeders/             # TipoDeHabitacionSeeder, HabitacionSeeder
├── routes/
│   └── api.php              # Todas las rutas API configuradas
└── config/
    ├── cors.php             # CORS configurado para localhost:5173
    └── services.php         # Configuración de Wompi
```

#### Frontend (Vue.js)
```
frontend/
├── src/
│   ├── views/               # HomePage, ReservaPage, ConfirmacionPage
│   ├── stores/              # bookingStore (Pinia)
│   ├── services/            # api.js (axios configurado)
│   ├── utils/               # currency.js (formateo COP)
│   └── router/              # Configuración de rutas
└── package.json             # Dependencias actualizadas
```

### CARACTERÍSTICAS IMPLEMENTADAS

✅ Sistema completo de reservas con validaciones
✅ Búsqueda de habitaciones disponibles por fechas y personas
✅ Calendario de disponibilidad por habitación
✅ Gestión de clientes (crear o actualizar)
✅ Integración con Wompi para pagos
✅ Webhook de Wompi para actualizar estados automáticamente
✅ 9 tipos de habitaciones con precios en COP
✅ 3 habitaciones por cada tipo (27 habitaciones totales)
✅ Formato de precios en pesos colombianos ($250.000 COP)
✅ Formato de fechas DD/MM/YYYY
✅ Validaciones: no fechas pasadas, fecha fin > fecha inicio
✅ Check-in: 15:00, Check-out: 12:00
✅ Estados de reserva: pendiente, confirmada, pagada, cancelada, completada
✅ Interfaz moderna y responsive
✅ Animaciones y efectos visuales
✅ Manejo de errores en español

### API ENDPOINTS

#### Habitaciones
- `GET /api/room-types` - Listar tipos de habitación
- `GET /api/rooms` - Listar habitaciones disponibles
- `GET /api/rooms/{id}` - Detalle de habitación
- `POST /api/rooms/check-availability` - Verificar disponibilidad
- `POST /api/rooms/available` - Buscar habitaciones disponibles

#### Reservas
- `POST /api/bookings` - Crear reserva
- `GET /api/bookings/{id}` - Detalle de reserva
- `PUT /api/bookings/{id}/cancel` - Cancelar reserva
- `POST /api/bookings/confirm-payment` - Confirmar pago

#### Pagos
- `POST /api/payments/wompi/create` - Crear transacción Wompi
- `POST /api/payments/wompi/webhook` - Webhook Wompi
- `GET /api/payments/wompi/{transactionId}` - Estado de transacción

#### Calendario
- `GET /api/calendar/{habitacionId}` - Disponibilidad de habitación
- `POST /api/calendar/block` - Bloquear fechas

### TIPOS DE HABITACIÓN (SEEDERS)

1. **Apartamento de 1 dormitorio** - $250.000 COP/noche (Capacidad: 4)
2. **Apartamento** - $350.000 COP/noche (Capacidad: 6)
3. **Habitación Doble Deluxe con vistas al lago** - $180.000 COP/noche (Capacidad: 2)
4. **Habitación Familiar Deluxe** - $220.000 COP/noche (Capacidad: 3)
5. **Apartamento** - $200.000 COP/noche (Capacidad: 2)
6. **Habitación Familiar con vistas al lago** - $230.000 COP/noche (Capacidad: 3)
7. **Habitación Doble con vistas al lago** - $170.000 COP/noche (Capacidad: 2)
8. **Habitación Familiar con baño privado** - $210.000 COP/noche (Capacidad: 3)
9. **Habitación Deluxe** - $190.000 COP/noche (Capacidad: 2)

### SOLUCIÓN DE PROBLEMAS

#### Error: Base de datos no existe
```sql
CREATE DATABASE rocamonti;
```

#### Error: Puerto 8000 ocupado
```bash
php artisan serve --port=8001
# Actualizar frontend/src/services/api.js con el nuevo puerto
```

#### Error: npm install falla en WSL
Ejecuta npm install desde PowerShell o CMD en Windows, no desde WSL.

#### Error: CORS
Verifica que el backend esté en `http://localhost:8000` y el frontend en `http://localhost:5173`

### PRÓXIMOS PASOS (OPCIONALES)

- [ ] Implementar generación de PDF para confirmación de reserva
- [ ] Agregar panel de administración
- [ ] Implementar sistema de autenticación de usuarios
- [ ] Agregar galería de imágenes reales de habitaciones
- [ ] Implementar envío de correos de confirmación
- [ ] Agregar multi-idioma (español/inglés)

---

**¡Sistema listo para usar! 🎉**

Si tienes algún problema, verifica:
1. MySQL está corriendo
2. La base de datos `rocamonti` existe
3. El backend está en el puerto 8000
4. El frontend está en el puerto 5173
5. Las dependencias están instaladas (composer y npm)
