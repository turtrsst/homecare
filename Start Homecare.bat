@echo off
title Homecare Laravel - PHP 8.4
cd /d C:\xampp\htdocs\homecare

echo ==========================================
echo   HOMECARE - Laravel Development Server
echo   PHP 8.4.26
echo ==========================================
echo.
echo Server: http://127.0.0.1:8000
echo.

start "" http://127.0.0.1:8000

C:\php84\php.exe -S 127.0.0.1:8000 -t public

pause