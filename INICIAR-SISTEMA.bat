@echo off
echo ====================================
echo HOTEL ROCAMONTI - SISTEMA DE RESERVAS
echo ====================================
echo.
echo Iniciando servidores...
echo.

start "Laravel Backend" cmd /k "cd backend && php artisan serve"
timeout /t 3 /nobreak >nul

start "Vue Frontend" cmd /k "cd frontend && npm run dev"

echo.
echo ====================================
echo SISTEMA INICIADO!
echo ====================================
echo.
echo Backend: http://localhost:8000
echo Frontend: http://localhost:5173
echo.
echo Presiona cualquier tecla para cerrar esta ventana...
pause >nul
