@echo off
taskkill /f /im mailpit.exe >nul 2>&1
start /b "" "%~dp0tools\mailpit.exe"
echo Mailpit demarre - boite mail : http://localhost:8025/
timeout /t 2 >nul
