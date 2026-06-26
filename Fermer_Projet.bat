@echo off
echo Fermeture des serveurs Golden Pos en cours...
taskkill /F /IM php.exe /T >nul 2>&1
taskkill /F /IM node.exe /T >nul 2>&1
echo.
echo Les serveurs ont ete fermes.
timeout /t 3 >nul
