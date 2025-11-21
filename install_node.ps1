# Install Node.js
Write-Host "Downloading Node.js installer..."
$nodeUrl = "https://nodejs.org/dist/v18.18.0/node-v18.18.0-x64.msi"
$outputPath = "$env:TEMP\node-installer.msi"

# Download Node.js installer
Invoke-WebRequest -Uri $nodeUrl -OutFile $outputPath

# Install Node.js silently
Write-Host "Installing Node.js..."
Start-Process msiexec.exe -Wait -ArgumentList "/I $outputPath /qn"

# Add Node.js to PATH
$nodePath = "$env:ProgramFiles\nodejs"
$currentPath = [System.Environment]::GetEnvironmentVariable("Path", "Machine")
if ($currentPath -notlike "*$nodePath*") {
    [System.Environment]::SetEnvironmentVariable("Path", "$currentPath;$nodePath", "Machine")
}

# Verify installation
$nodeVersion = node --version 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "Node.js installed successfully. Version: $nodeVersion"
} else {
    Write-Host "Node.js installation may have failed. Please restart your terminal and run 'node --version' to verify."
}

# Clean up
Remove-Item $outputPath -Force
