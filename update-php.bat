@echo off
REM Script para actualizar PHP en XAMPP
echo ====================================
echo   Actualizacion de PHP a 8.4
echo ====================================
echo.

REM Verificar que existe XAMPP
if not exist "C:\xampp\php" (
    echo ERROR: No se encontro C:\xampp\php
    pause
    exit /b 1
)

echo [1/5] Creando backup de PHP actual...
if not exist "C:\xampp\php_backup_8.2.4" (
    xcopy "C:\xampp\php" "C:\xampp\php_backup_8.2.4" /E /I /H /Y
    echo Backup creado en C:\xampp\php_backup_8.2.4
) else (
    echo Backup ya existe, saltando...
)

echo.
echo [2/5] Por favor, descarga PHP 8.4 Thread Safe x64 desde:
echo https://windows.php.net/download/
echo.
echo Extrae el archivo ZIP descargado a una carpeta temporal
echo.
set /p PHP84_PATH="Ingresa la ruta completa donde extrajiste PHP 8.4 (ej: C:\temp\php-8.4.x): "

if not exist "%PHP84_PATH%\php.exe" (
    echo ERROR: No se encontro php.exe en %PHP84_PATH%
    pause
    exit /b 1
)

echo.
echo [3/5] Respaldando php.ini actual...
if exist "C:\xampp\php\php.ini" (
    copy "C:\xampp\php\php.ini" "C:\xampp\php.ini.backup"
    echo php.ini respaldado
)

echo.
echo [4/5] Reemplazando archivos de PHP...
echo NOTA: Puede que necesites cerrar XAMPP y servicios de Apache/MySQL
pause

REM Eliminar archivos antiguos (excepto backup)
rd /s /q "C:\xampp\php_temp" 2>nul
move "C:\xampp\php" "C:\xampp\php_temp"

REM Copiar nueva versión
xcopy "%PHP84_PATH%" "C:\xampp\php" /E /I /H /Y

echo.
echo [5/5] Configurando php.ini...

REM Copiar php.ini de desarrollo como base
if exist "C:\xampp\php\php.ini-development" (
    copy "C:\xampp\php\php.ini-development" "C:\xampp\php\php.ini"
)

REM Restaurar configuraciones personalizadas del backup si existe
if exist "C:\xampp\php.ini.backup" (
    echo.
    echo IMPORTANTE: Se creo un php.ini base.
    echo Tu php.ini anterior esta en C:\xampp\php.ini.backup
    echo Revisa las extensiones que necesitas habilitar.
)

echo.
echo ====================================
echo   Actualizacion completada
echo ====================================
echo.
echo Ejecuta: php -v
echo Para verificar la version
echo.
echo Si algo salio mal, puedes restaurar desde:
echo C:\xampp\php_backup_8.2.4
echo.
pause
