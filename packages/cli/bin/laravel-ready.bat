@echo off
setlocal
set "DIR=%~dp0"
if exist "%DIR%laravel-ready.exe" (
    "%DIR%laravel-ready.exe" %*
    exit /b %ERRORLEVEL%
)
echo laravel-ready.exe not found. Run: composer install
exit /b 1
