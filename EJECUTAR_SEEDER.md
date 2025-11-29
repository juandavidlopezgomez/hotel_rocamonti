# 🎯 EJECUTAR SEEDER DE RESERVAS

## Instrucciones para poblar la base de datos con datos de prueba

### Opción 1: Ejecutar solo el seeder de reservas

```bash
cd backend
php artisan db:seed --class=ReservasAleatoriasSeeder
```

### Opción 2: Limpiar y poblar toda la base de datos desde cero

```bash
cd backend
php artisan migrate:fresh --seed
```

⚠️ **ADVERTENCIA**: `migrate:fresh` borrará TODOS los datos existentes.

---

## 📊 Datos que se crearán

El seeder generará aproximadamente **46 reservas** distribuidas así:

### Por Filtro de Fecha:
- ✅ **3 Llegadas Hoy** (estado: confirmada)
- ✅ **2 Salidas Hoy** (estado: activa)
- ✅ **4 Llegadas Mañana** (estado: confirmada)
- ✅ **6 Próximos 7 días** (días 2-7, estado: confirmada)
- ✅ **5 Huéspedes Activos** (actualmente en el hotel)
- ✅ **8 Este Mes** (resto del mes, estado: confirmada)
- ✅ **10 Reservas Futuras** (próximos 2-3 meses)

### Por Estado:
- ✅ **Confirmadas**: Pendiente de check-in (PAGADAS)
- 🏨 **Activas**: Huésped en hotel (PAGADAS)
- ✔️ **Completadas**: 5 reservas con check-out realizado (PAGADAS)
- ❌ **Canceladas**: 3 reservas canceladas (PAGADAS)

---

## 💳 IMPORTANTE

### ✅ TODAS LAS RESERVAS ESTÁN 100% PAGADAS

El sistema requiere pago obligatorio al crear cada reserva. Todas las reservas generadas tienen el pago procesado.

---

## 🧪 Verificar que funcione

Después de ejecutar el seeder:

1. Abre el panel admin: `http://localhost:5173/admin`
2. Ve a la sección de **Reservas**
3. Prueba cada filtro:
   - 📋 Todas (100% Pagadas)
   - 📅 Hoy
   - 🌅 Mañana
   - 📆 Próximos 7 días
   - 🗓️ Este mes

4. Prueba cada tab:
   - Todas
   - Hoy
   - Mañana
   - Esta Semana
   - Activas

5. Prueba el filtro de estados:
   - Confirmadas - Pendiente Check-in
   - Activas - Huésped en Hotel
   - Completadas - Check-out Realizado
   - Canceladas

---

## 🔧 Si hay errores

Si el seeder falla con "No hay habitaciones":

```bash
cd backend
php artisan db:seed --class=HabitacionSeeder
php artisan db:seed --class=ReservasAleatoriasSeeder
```

---

## 📝 Nota

Los datos son completamente aleatorios pero realistas:
- Nombres y apellidos hispanos comunes
- Emails generados automáticamente
- Teléfonos colombianos (formato 3XXXXXXXXX)
- Cédulas aleatorias de 8 dígitos
- Precios calculados según tipo de habitación y noches
