@echo off
chcp 65001 >nul
echo ================================================
echo   Probando configuración CORS
echo ================================================
echo.

cd C:\xampp\htdocs\hotel-rocamonti\backend

echo Probando endpoint /api/test...
echo.

C:\xampp\php\php.exe -r "echo file_get_contents('http://localhost:8000/api/test');"

echo.
echo.
echo ================================================
echo Probando endpoint /api/room-types...
echo.

C:\xampp\php\php.exe -r "echo file_get_contents('http://localhost:8000/api/room-types?num_adultos=2');"

echo.
echo.
echo ================================================
pause
