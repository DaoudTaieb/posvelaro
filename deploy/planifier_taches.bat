@echo off
rem =====================================================================
rem  Golden Pos - Enregistrement des taches planifiees Windows
rem
rem  A executer UNE fois, en tant qu'administrateur, sur le poste serveur :
rem    1. "GoldenPos-Planificateur" : php artisan schedule:run chaque minute
rem       (lance le smoke test quotidien de 07:30 defini dans routes/console.php)
rem    2. "GoldenPos-Sauvegarde"    : sauvegarde de la base velaro chaque jour a 23:30
rem
rem  Pour supprimer les taches :
rem    schtasks /delete /tn "GoldenPos-Planificateur" /f
rem    schtasks /delete /tn "GoldenPos-Sauvegarde" /f
rem =====================================================================
setlocal
set "PROJET=%~dp0.."
for %%i in ("%PROJET%") do set "PROJET=%%~fi"
if "%PHP_EXE%"=="" set "PHP_EXE=C:\xampp\php\php.exe"

net session >nul 2>&1
if errorlevel 1 (
    echo [ERREUR] Lancez ce script en tant qu'administrateur ^(clic droit, Executer en tant qu'administrateur^).
    exit /b 1
)

schtasks /create /f /tn "GoldenPos-Planificateur" /sc minute /mo 1 /ru SYSTEM ^
    /tr "\"%PHP_EXE%\" \"%PROJET%\artisan\" schedule:run"
schtasks /create /f /tn "GoldenPos-Sauvegarde" /sc daily /st 23:30 /ru SYSTEM ^
    /tr "\"%PROJET%\deploy\sauvegarde_velaro.bat\""

echo.
echo Taches enregistrees :
schtasks /query /tn "GoldenPos-Planificateur"
schtasks /query /tn "GoldenPos-Sauvegarde"
endlocal
