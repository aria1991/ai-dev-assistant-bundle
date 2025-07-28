@echo off
REM Cross-Platform Branch Protection Setup Launcher for Windows
REM This script attempts to run the appropriate setup script based on available tools

echo === GitHub Branch Protection Setup ===
echo Repository: aria1991/ai-dev-assistant-bundle
echo.

REM Check if PowerShell is available (prefer on Windows)
where powershell >nul 2>&1
if %ERRORLEVEL% == 0 (
    echo Running PowerShell script...
    powershell -ExecutionPolicy Bypass -File .github\scripts\setup-branch-protection.ps1
    goto :end
)

REM Check if we're in Git Bash or have bash available
where bash >nul 2>&1
if %ERRORLEVEL% == 0 (
    echo Running cross-platform shell script...
    bash .github\scripts\setup-branch-protection.sh
    goto :end
)

REM If neither is available, provide manual instructions
echo ERROR: Neither PowerShell nor Bash is available.
echo.
echo Please use one of the following:
echo 1. PowerShell: powershell -ExecutionPolicy Bypass -File .github\scripts\setup-branch-protection.ps1
echo 2. Git Bash: bash .github\scripts\setup-branch-protection.sh
echo 3. Manual setup guide: .github\BRANCH_PROTECTION_SETUP.md
echo.
echo If you need to install GitHub CLI: https://cli.github.com/

:end
pause
