@echo off
REM HTTPS Setup Verification Script

echo ========================================
echo HTTPS Setup Verification
echo ========================================
echo.

set WAMP_PATH=D:\Programming\wamp
set APACHE_PATH=%WAMP_PATH%\bin\apache\apache2.4.62.1
set SSL_DIR=%APACHE_PATH%\conf\ssl

echo Checking HTTPS setup...
echo.

REM Check 1: SSL directory exists
echo [1] Checking SSL directory...
if exist "%SSL_DIR%" (
    echo     [OK] SSL directory exists
) else (
    echo     [FAIL] SSL directory NOT found
    echo     Location: %SSL_DIR%
)
echo.

REM Check 2: Certificate files exist
echo [2] Checking SSL certificate files...
if exist "%SSL_DIR%\server.crt" (
    echo     [OK] server.crt exists
) else (
    echo     [FAIL] server.crt NOT found
)

if exist "%SSL_DIR%\server.key" (
    echo     [OK] server.key exists
) else (
    echo     [FAIL] server.key NOT found
)
echo.

REM Check 3: httpd-ssl.conf exists
echo [3] Checking httpd-ssl.conf...
if exist "%APACHE_PATH%\conf\extra\httpd-ssl.conf" (
    echo     [OK] httpd-ssl.conf exists
) else (
    echo     [FAIL] httpd-ssl.conf NOT found
)
echo.

REM Check 4: Apache listening on port 443
echo [4] Checking if Apache is listening on port 443...
netstat -an | findstr ":443.*LISTENING" >nul
if %errorLevel% equ 0 (
    echo     [OK] Apache is listening on port 443
) else (
    echo     [FAIL] Apache is NOT listening on port 443
    echo     This means HTTPS is not active yet
)
echo.

REM Check 5: Firewall rule
echo [5] Checking firewall rule...
netsh advfirewall firewall show rule name="WAMP HTTPS" >nul 2>&1
if %errorLevel% equ 0 (
    echo     [OK] Firewall rule exists
) else (
    echo     [FAIL] Firewall rule NOT found
)
echo.

echo ========================================
echo Summary
echo ========================================
echo.
echo If all checks show [OK], HTTPS should work!
echo Test at: https://192.168.1.6/cics-attendance-system
echo.
echo If any checks show [FAIL]:
echo 1. Run setup-https-simple.bat as Administrator
echo 2. Restart WAMP
echo 3. Run this verification script again
echo.
pause
