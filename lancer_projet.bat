@echo off
cd /d "%~dp0"
set "PIDFILE=%~dp0.golden_pos.pid"

echo =========================================
echo    Lancement du projet Golden Pos
echo =========================================

rem Arrete les serveurs lances precedemment par ces scripts (et uniquement ceux-la)
if exist "%PIDFILE%" (
    for /f "usebackq delims=" %%p in ("%PIDFILE%") do (
        if not "%%p"=="" taskkill /F /T /PID %%p >nul 2>&1
    )
    del /q "%PIDFILE%"
)

echo.
echo Lancement du serveur backend (Laravel PHP)...
for /f %%p in ('powershell -NoProfile -Command "$p = Start-Process cmd.exe -ArgumentList '/k php artisan serve' -WindowStyle Minimized -PassThru; $p.Id"') do >>"%PIDFILE%" echo %%p

echo Lancement du serveur frontend (Vite NPM)...
for /f %%p in ('powershell -NoProfile -Command "$p = Start-Process cmd.exe -ArgumentList '/k npm run dev' -WindowStyle Minimized -PassThru; $p.Id"') do >>"%PIDFILE%" echo %%p

echo.
echo Les serveurs sont lances dans de nouvelles fenetres (PID dans .golden_pos.pid).
echo Ouverture du navigateur web dans quelques secondes...
timeout /t 3 /nobreak >nul

start http://127.0.0.1:8000

exit
