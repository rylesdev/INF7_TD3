@echo off
cd /d "%~dp0"
echo ============================================================
echo  COLOCATION.COM - Installation (Windows WAMP)
echo ============================================================
echo.

echo [1/9] Installation des dependances Composer...
call composer install --no-interaction
if %errorlevel% neq 0 ( echo ERREUR: composer install a echoue. & pause & exit /b 1 )

echo [2/9] Copie du fichier .env...
if not exist .env (
    copy .env.example .env
    echo  .env cree depuis .env.example - veuillez configurer DATABASE_URL et JWT_PASSPHRASE
    pause
)

echo [3/9] Suppression et recreation de la base de donnees...
call php bin/console doctrine:database:drop --force --if-exists
if %errorlevel% neq 0 ( echo ERREUR: suppression BDD echouee. Verifiez que WAMP est demarre. & pause & exit /b 1 )
call php bin/console doctrine:database:create
if %errorlevel% neq 0 ( echo ERREUR: creation BDD echouee. & pause & exit /b 1 )

echo [4/9] Generation et execution des migrations...
del /q migrations\Version*.php 2>nul
call php bin/console make:migration --no-interaction
if %errorlevel% neq 0 ( echo ERREUR: make:migration a echoue. & pause & exit /b 1 )
call php bin/console doctrine:migrations:migrate --no-interaction
if %errorlevel% neq 0 ( echo ERREUR: migrations echouees. & pause & exit /b 1 )

echo [5/9] Chargement des fixtures...
call php bin/console doctrine:fixtures:load --no-interaction
if %errorlevel% neq 0 ( echo ERREUR: fixtures echouees. & pause & exit /b 1 )

echo [6/9] Generation des cles JWT...
if not exist config\jwt mkdir config\jwt
if not exist config\jwt\private.pem (
    for /f "tokens=2 delims==" %%a in ('findstr /i "JWT_PASSPHRASE" .env') do set JWT_PP=%%a
    openssl genrsa -passout pass:%JWT_PP% -out config\jwt\private.pem -aes256 4096
    if %errorlevel% neq 0 ( echo ERREUR: generation cle privee JWT echouee. & pause & exit /b 1 )
    openssl rsa -pubout -passin pass:%JWT_PP% -in config\jwt\private.pem -out config\jwt\public.pem
    if %errorlevel% neq 0 ( echo ERREUR: generation cle publique JWT echouee. & pause & exit /b 1 )
    echo  Cles JWT generees.
) else (
    echo  Cles JWT deja presentes.
)

echo [7/9] Creation des dossiers d'upload...
if not exist public\uploads\annonces mkdir public\uploads\annonces
if not exist public\uploads\profils mkdir public\uploads\profils
if not exist public\uploads\quittances mkdir public\uploads\quittances
echo  Dossiers crees.

echo [8/9] Nettoyage du cache...
call php bin/console cache:clear
if %errorlevel% neq 0 echo AVERTISSEMENT: cache:clear a echoue.

echo [9/9] Configuration de Mailpit (serveur mail de test)...
if not exist tools\mailpit.exe (
    echo  Telechargement de Mailpit...
    powershell -Command "Invoke-WebRequest -Uri 'https://github.com/axllent/mailpit/releases/latest/download/mailpit-windows-amd64.zip' -OutFile 'tools\mailpit.zip'"
    powershell -Command "Expand-Archive -Path 'tools\mailpit.zip' -DestinationPath 'tools' -Force"
    del /q tools\mailpit.zip 2>nul
    del /q tools\LICENSE tools\README.md 2>nul
    if not exist tools\mailpit.exe goto mailpit_absent
    echo  Mailpit telecharge.
)
if not exist tools\mailpit.exe goto mailpit_absent

schtasks /query /tn "Mailpit-Colocation" >nul 2>&1
if %errorlevel% neq 0 (
    echo  Enregistrement de Mailpit dans le Planificateur de taches...
    schtasks /create /tn "Mailpit-Colocation" /tr "powershell -WindowStyle Hidden -Command \"Start-Process '%~dp0tools\mailpit.exe' -WindowStyle Hidden\"" /sc onlogon /ru "%USERNAME%" /f >nul
    if %errorlevel% neq 0 (
        echo  AVERTISSEMENT: echec enregistrement - relancez en tant qu'Administrateur.
    )
)
taskkill /f /im mailpit.exe >nul 2>&1
powershell -Command "Start-Process '%~dp0tools\mailpit.exe' -WindowStyle Hidden"
echo  Mailpit demarre en arriere-plan - boite mail : http://localhost:8025/
goto fin

:mailpit_absent
echo  Mailpit non trouve dans tools\mailpit.exe - email desactive.
echo  Pour activer : telechargez mailpit-windows-amd64.zip sur github.com/axllent/mailpit/releases
echo  et placez mailpit.exe dans le dossier tools\ du projet.

:fin
echo.
echo ============================================================
echo  Installation terminee !
echo.
echo  Comptes de test :
echo    Proprietaire : proprio@colocation.com / Proprio1234!
echo    Locataire    : locataire@colocation.com / Locataire1234!
echo.
echo  Acces : http://localhost/INF7_TD3/public/
echo  Mails : http://localhost:8025/ (si Mailpit installe)
echo ============================================================
pause
