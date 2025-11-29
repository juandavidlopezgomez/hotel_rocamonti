@echo off
echo Deshabilitando SSL en Composer...
composer config --global disable-tls true 2>nul
composer config --global secure-http false 2>nul
echo.
echo Instalando dependencias sin SSL...
composer install --ignore-platform-reqs --no-scripts
echo.
pause
