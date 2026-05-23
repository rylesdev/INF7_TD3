@echo off
echo ============================================================
echo  COLOCATION.COM - Installation (Windows WAMP)
echo ============================================================
echo.

echo [1/8] Installation des dependances Composer...
call composer install --no-interaction
if %errorlevel% neq 0 ( echo ERREUR: composer install a echoue. & pause & exit /b 1 )

echo [2/8] Copie du fichier .env...
if not exist .env (
    copy .env.example .env
    echo  .env cree depuis .env.example - veuillez configurer DATABASE_URL et JWT_PASSPHRASE
    pause
)

echo [3/8] Suppression et recreation de la base de donnees...
call php bin/console doctrine:database:drop --force --if-exists
if %errorlevel% neq 0 ( echo ERREUR: suppression BDD echouee. Verifiez que WAMP est demarre. & pause & exit /b 1 )
call php bin/console doctrine:database:create
if %errorlevel% neq 0 ( echo ERREUR: creation BDD echouee. & pause & exit /b 1 )

echo [4/8] Generation et execution des migrations...
del /q migrations\Version*.php 2>nul
call php bin/console make:migration --no-interaction
if %errorlevel% neq 0 ( echo ERREUR: make:migration a echoue. & pause & exit /b 1 )
call php bin/console doctrine:migrations:migrate --no-interaction
if %errorlevel% neq 0 ( echo ERREUR: migrations echouees. & pause & exit /b 1 )

echo [5/8] Chargement des fixtures...
call php bin/console doctrine:fixtures:load --no-interaction
if %errorlevel% neq 0 ( echo ERREUR: fixtures echouees. & pause & exit /b 1 )

echo [6/8] Generation des cles JWT...
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

echo [7/8] Creation des dossiers d'upload...
if not exist public\uploads\annonces mkdir public\uploads\annonces
if not exist public\uploads\profils mkdir public\uploads\profils
if not exist public\uploads\quittances mkdir public\uploads\quittances
echo  Dossiers crees.

echo [8/8] Nettoyage du cache...
call php bin/console cache:clear
if %errorlevel% neq 0 ( echo AVERTISSEMENT: cache:clear a echoue. & )

echo.
echo ============================================================
echo  Installation terminee !
echo.
echo  Comptes de test :
echo    Proprietaire : proprio@colocation.com / Proprio1234!
echo    Locataire    : locataire@colocation.com / Locataire1234!
echo.
echo  Acces : http://localhost/INF7_TD3/public/
echo ============================================================
pause
