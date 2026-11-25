@echo off
chcp 65001 >nul
title UAS - University Archiving System Server
color 0A

echo ========================================================
echo       Ibb University - Archiving System
echo ========================================================
echo.
echo [1/2] Opening browser...
start http://127.0.0.1:8000

echo [2/2] Starting Laravel Server...
echo.

cd /d "%~dp0Demo_Version"
php -d upload_max_filesize=50M -d post_max_size=64M artisan serve --host=0.0.0.0 --port=8000

pause
