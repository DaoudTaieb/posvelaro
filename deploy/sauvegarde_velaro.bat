@echo off
rem =====================================================================
rem  Golden Pos - Sauvegarde quotidienne de la base PostgreSQL "velaro"
rem
rem  - lit les parametres DB_* du fichier .env du projet ;
rem  - cree une archive compressee (format custom pg_dump) horodatee ;
rem  - verifie que l'archive est lisible (pg_restore --list) ;
rem  - supprime les sauvegardes de plus de %JOURS_CONSERVATION% jours.
rem
rem  Lecture seule sur la base : aucune donnee n'est modifiee.
rem  Planification : voir deploy\planifier_taches.bat
rem  Restauration (sur decision de l'administrateur uniquement) :
rem    pg_restore -h 127.0.0.1 -U postgres -d velaro --clean --if-exists <fichier.dump>
rem =====================================================================
setlocal EnableDelayedExpansion

set "PROJET=%~dp0.."
if "%PG_BIN%"=="" set "PG_BIN=C:\Program Files\PostgreSQL\17\bin"
if "%DOSSIER_SAUVEGARDE%"=="" set "DOSSIER_SAUVEGARDE=C:\GoldenPos\sauvegardes"
if "%JOURS_CONSERVATION%"=="" set "JOURS_CONSERVATION=30"

rem --- Parametres de connexion lus dans .env ---------------------------
set "DB_HOST=127.0.0.1"
set "DB_PORT=5432"
set "DB_DATABASE=velaro"
set "DB_USERNAME=postgres"
set "DB_PASSWORD="
for /f "usebackq eol=# tokens=1,* delims==" %%a in ("%PROJET%\.env") do (
    set "CLE=%%a"
    set "VAL=%%b"
    if defined VAL set "VAL=!VAL:"=!"
    if /i "!CLE!"=="DB_HOST" set "DB_HOST=!VAL!"
    if /i "!CLE!"=="DB_PORT" set "DB_PORT=!VAL!"
    if /i "!CLE!"=="DB_DATABASE" set "DB_DATABASE=!VAL!"
    if /i "!CLE!"=="DB_USERNAME" set "DB_USERNAME=!VAL!"
    if /i "!CLE!"=="DB_PASSWORD" set "DB_PASSWORD=!VAL!"
)
set "PGPASSWORD=%DB_PASSWORD%"

if not exist "%PG_BIN%\pg_dump.exe" (
    echo [ERREUR] pg_dump introuvable dans "%PG_BIN%". Definissez la variable PG_BIN.
    exit /b 2
)
if not exist "%DOSSIER_SAUVEGARDE%" mkdir "%DOSSIER_SAUVEGARDE%"

rem --- Horodatage AAAAMMJJ_HHMM independant de la langue de Windows -----
for /f %%t in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd_HHmm"') do set "HORODATAGE=%%t"
set "FICHIER=%DOSSIER_SAUVEGARDE%\%DB_DATABASE%_%HORODATAGE%.dump"
set "JOURNAL=%DOSSIER_SAUVEGARDE%\sauvegardes.log"

echo [%DATE% %TIME%] Sauvegarde de %DB_DATABASE% vers "%FICHIER%" >> "%JOURNAL%"
"%PG_BIN%\pg_dump.exe" -h %DB_HOST% -p %DB_PORT% -U %DB_USERNAME% -F c -Z 6 -f "%FICHIER%" %DB_DATABASE%
if errorlevel 1 (
    echo [%DATE% %TIME%] ECHEC de pg_dump >> "%JOURNAL%"
    echo [ERREUR] La sauvegarde a echoue. Voir "%JOURNAL%".
    exit /b 1
)

rem --- Verification : l'archive doit etre lisible ----------------------
"%PG_BIN%\pg_restore.exe" --list "%FICHIER%" > nul
if errorlevel 1 (
    echo [%DATE% %TIME%] ECHEC : archive illisible "%FICHIER%" >> "%JOURNAL%"
    echo [ERREUR] L'archive produite est illisible.
    exit /b 1
)

for %%f in ("%FICHIER%") do set "TAILLE=%%~zf"
echo [%DATE% %TIME%] OK (%TAILLE% octets) >> "%JOURNAL%"
echo Sauvegarde OK : "%FICHIER%" (%TAILLE% octets)

rem --- Rotation : suppression des sauvegardes anciennes ----------------
forfiles /p "%DOSSIER_SAUVEGARDE%" /m "%DB_DATABASE%_*.dump" /d -%JOURS_CONSERVATION% /c "cmd /c del /q @path" 2>nul

endlocal
exit /b 0
