@echo off
rem =====================================================================
rem  Golden Pos - Mise en production / mise a jour sur le poste serveur
rem
rem  A lancer apres chaque recuperation du code (git pull) :
rem    1. verifie le fichier .env (production, debug desactive) ;
rem    2. installe les dependances PHP sans les outils de developpement ;
rem    3. applique les migrations Golden Pos (idempotentes) sur la base velaro ;
rem    4. met en cache configuration, routes et vues ;
rem    5. controle toutes les pages (smoke test).
rem
rem  Prerequis : Apache configure (deploy\apache\golden-pos.conf), taches planifiees
rem  enregistrees (deploy\planifier_taches.bat), sauvegarde du jour effectuee.
rem =====================================================================
setlocal
cd /d "%~dp0.."
if "%PHP_EXE%"=="" set "PHP_EXE=C:\xampp\php\php.exe"

if not exist ".env" (
    echo [ERREUR] Fichier .env absent. Copiez deploy\env.production.example en .env et completez-le.
    exit /b 1
)
findstr /b /i /c:"APP_DEBUG=false" .env >nul || (
    echo [ERREUR] APP_DEBUG doit valoir false en production ^(fichier .env^).
    exit /b 1
)
findstr /b /i /c:"APP_ENV=production" .env >nul || (
    echo [ERREUR] APP_ENV doit valoir production ^(fichier .env^).
    exit /b 1
)

echo == Sauvegarde de la base avant mise a jour
call "%~dp0sauvegarde_velaro.bat" || exit /b 1

echo == Dependances PHP
call composer install --no-dev --optimize-autoloader --no-interaction || exit /b 1

echo == Cle d'application
findstr /b /c:"APP_KEY=base64:" .env >nul || "%PHP_EXE%" artisan key:generate --force

echo == Migrations Golden Pos (idempotentes, aucune table du logiciel de bureau n'est modifiee)
"%PHP_EXE%" artisan migrate --force || exit /b 1

echo == Caches de production
rem Le serveur de developpement Vite n'est pas utilise en production
if exist "public\hot" del /q "public\hot"
"%PHP_EXE%" artisan optimize:clear
"%PHP_EXE%" artisan optimize || exit /b 1

echo == Controle de toutes les pages
"%PHP_EXE%" artisan app:smoke-test || (
    echo [ATTENTION] Des pages sont en erreur : voir storage\logs.
    exit /b 1
)

echo.
echo Mise en production terminee.
endlocal
