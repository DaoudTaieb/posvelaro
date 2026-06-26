@echo off
cd /d "%~dp0"

echo =========================================
echo    Lancement du projet POS Velaro
echo =========================================

echo.
echo Lancement du serveur backend (Laravel PHP)...
start /MIN "Laravel Server" cmd /k "php artisan serve"

echo Lancement du serveur frontend (Vite NPM)...
start /MIN "Vite Dev Server" cmd /k "npm run dev"

echo.
echo Les serveurs sont lances dans de nouvelles fenetres.
echo Ouverture du navigateur web dans quelques secondes...
timeout /t 3 /nobreak >nul

start http://127.0.0.1:8000

exit
