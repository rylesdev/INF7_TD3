@echo off
echo ============================================================
echo  MAILPIT - Serveur mail de SIMULATION (dev uniquement)
echo  Chemin : INF7_TD3\tools\mailpit.exe
echo  Interface web : http://localhost:8025/
echo.
echo  NOTE : Mailpit simule la reception d'emails sans en envoyer.
echo  En production, utilisez un vrai service SMTP (SendGrid, etc.)
echo  et supprimez cette simulation.
echo ============================================================
taskkill /f /im mailpit.exe >nul 2>&1
start /b "" "%~dp0tools\mailpit.exe"
echo Mailpit demarre en arriere-plan.
timeout /t 2 >nul
