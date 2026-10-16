@echo off
cd /d "%~dp0"
set "PIDFILE=%~dp0.golden_pos.pid"

echo Fermeture des serveurs Golden Pos en cours...

if not exist "%PIDFILE%" (
    echo Aucun serveur lance par les scripts Golden Pos n'est enregistre.
    echo ^(fichier .golden_pos.pid absent : rien a arreter^)
    timeout /t 3 >nul
    exit /b 0
)

rem N'arrete que les processus lances par lancer_projet.bat / lancer_invisible.vbs
rem (et leurs processus enfants), jamais les autres php.exe ou node.exe.
for /f "usebackq delims=" %%p in ("%PIDFILE%") do (
    if not "%%p"=="" taskkill /F /T /PID %%p >nul 2>&1
)
del /q "%PIDFILE%"

rem Vite arrete de force laisse public\hot derriere lui : Laravel chercherait
rem alors un serveur Vite inexistant.
if exist "public\hot" del /q "public\hot"

echo.
echo Les serveurs ont ete fermes.
timeout /t 3 >nul
