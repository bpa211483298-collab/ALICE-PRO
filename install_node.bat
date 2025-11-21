@echo off
echo Installing Node.js...

:: Download Node.js installer
curl -L -o node-installer.msi https://nodejs.org/dist/v18.18.0/node-v18.18.0-x64.msi

:: Install Node.js silently
msiexec /i node-installer.msi /qn /norestart

:: Add Node.js to PATH temporarily for the current session
setx PATH "%%PROGRAMFILES%%\nodejs;%PATH%"

echo Node.js installation complete. Please restart your terminal for changes to take effect.
