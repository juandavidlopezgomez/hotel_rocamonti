# 🏨 Hotel Rocamonti - Sistema de Reservas

Sistema completo de reservas hoteleras con integración de pagos Wompi, desarrollado con Laravel (backend) y Vue.js (frontend).

## 🚀 Inicio Rápido

### Backend
```bash
cd backend
composer install
composer require guzzlehttp/guzzle

# Crear base de datos 'rocamonti' en MySQL
php artisan migrate:fresh --seed
php artisan serve
```

### Frontend
```bash
cd frontend
npm install
npm run dev
```

## 📋 Requisitos

- PHP 8.1+
- MySQL 5.7+
- Composer
- Node.js 20+
- XAMPP (recomendado)

## 🌐 URLs

- **Backend API**: http://localhost:8000
- **Frontend**: http://localhost:5173
- **API Test**: http://localhost:8000/api/test

## ✨ Características

- ✅ Sistema de reservas completo con 6 pasos
- ✅ Integración con Wompi para pagos en COP
- ✅ 9 tipos de habitaciones / 27 habitaciones totales
- ✅ Búsqueda por fechas y número de personas
- ✅ Calendario de disponibilidad
- ✅ Gestión automática de estados
- ✅ Webhook de Wompi configurado
- ✅ Interfaz responsive y moderna
- ✅ Formato de precios en pesos colombianos
- ✅ Validaciones de fechas y capacidad

## 📱 Flujo de Usuario

1. **Inicio**: Página de bienvenida con imagen full-screen
2. **Búsqueda**: Selección de fechas y número de personas
3. **Selección**: Ver y elegir tipo de habitación
4. **Habitación**: Elegir habitación específica
5. **Datos**: Formulario de información del cliente
6. **Pago**: Integración con Wompi para pago seguro
7. **Confirmación**: Resumen completo de la reserva

## 🏗️ Estructura del Proyecto

```
hotel-rocamonti/
├── backend/              # Laravel API
│   ├── app/Models/      # 7 modelos con relaciones
│   ├── app/Http/Controllers/  # 4 controladores
│   ├── database/migrations/   # 7 migraciones
│   └── database/seeders/      # Datos de prueba
│
├── frontend/            # Vue.js 3
│   ├── src/views/      # 3 vistas principales
│   ├── src/stores/     # Pinia store
│   ├── src/services/   # API client
│   └── src/utils/      # Helpers
│
├── INSTRUCCIONES.md    # Guía completa
└── README.md          # Este archivo
```

## 💳 Wompi - Modo Prueba

Credenciales de prueba configuradas en `.env`:
- **Public Key**: pub_test_xP11VTEBcbLmeyPFPqPbhwrVWB5JbJVN
- **Private Key**: prv_test_zj270NtMnY4Nx6PLxqXnGJQBy67Jinvh
- **Integrity Secret**: test_integrity_fhf6DIbYE3mRoAOr5jhnYYAd2FSEOAjY
- **API URL**: https://sandbox.wompi.co/v1

### Tarjetas de Prueba Wompi

Para probar pagos exitosos:
- **Tarjeta**: 4242 4242 4242 4242
- **Vencimiento**: Cualquier fecha futura (ej: 12/28)
- **CVC**: Cualquier 3 dígitos (ej: 123)
- **Nombre**: Cualquier nombre

Para probar pagos rechazados:
- **Tarjeta**: 4111 1111 1111 1111

## 🛏️ Tipos de Habitación

1. Apartamento de 1 dormitorio - $250.000 COP
2. Apartamento (6 personas) - $350.000 COP
3. Habitación Doble Deluxe vista lago - $180.000 COP
4. Habitación Familiar Deluxe - $220.000 COP
5. Apartamento (2 personas) - $200.000 COP
6. Habitación Familiar vista lago - $230.000 COP
7. Habitación Doble vista lago - $170.000 COP
8. Habitación Familiar baño privado - $210.000 COP
9. Habitación Deluxe - $190.000 COP

## 📚 Documentación Completa

Ver [INSTRUCCIONES.md](INSTRUCCIONES.md) para:
- Instalación detallada
- Configuración de Wompi
- Endpoints de API
- Solución de problemas
- Estructura completa

## 🐛 Solución de Problemas

**Base de datos no existe**
```sql
CREATE DATABASE rocamonti;
```

**Puerto ocupado**
```bash
php artisan serve --port=8001
```

**npm install falla en WSL**
Ejecutar desde PowerShell/CMD en Windows

## 📄 Licencia

Proyecto educativo - Hotel Rocamonti

---

Desarrollado con ❤️ usando Laravel + Vue.js + Wompi
