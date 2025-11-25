@echo off
REM HTTPS Setup - Fixed OpenSSL Path
REM Run as Administrator

echo ========================================
echo WAMP HTTPS Setup - OpenSSL Fix
echo ========================================
echo.

set WAMP_PATH=D:\Programming\wamp
set APACHE_PATH=%WAMP_PATH%\bin\apache\apache2.4.62.1
set SSL_DIR=%APACHE_PATH%\conf\ssl
set OPENSSL_CONF=%APACHE_PATH%\conf\openssl.cnf
set IP=192.168.1.6

REM Check if running as admin
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Must run as Administrator!
    pause
    exit /b 1
)

echo Step 1: Creating SSL directory...
if not exist "%SSL_DIR%" mkdir "%SSL_DIR%"
echo Done!
echo.

echo Step 2: Generating SSL certificate with correct OpenSSL config...
cd /d "%APACHE_PATH%\bin"

REM Set OpenSSL config environment variable
set OPENSSL_CONF=%APACHE_PATH%\conf\openssl.cnf

REM Check if openssl.cnf exists
if not exist "%OPENSSL_CONF%" (
    echo Warning: openssl.cnf not found at expected location
    echo Trying to generate certificate anyway...
)

REM Generate certificate with explicit config
openssl.exe req -x509 -nodes -days 365 -newkey rsa:2048 ^
    -config "%OPENSSL_CONF%" ^
    -keyout "%SSL_DIR%\server.key" ^
    -out "%SSL_DIR%\server.crt" ^
    -subj "/C=PH/ST=Zamboanga/L=Pagadian/O=ZPPSU/OU=CICS/CN=%IP%"

if %errorLevel% neq 0 (
    echo.
    echo ERROR: Certificate generation failed!
    echo Trying alternative method without config file...
    echo.
    
    REM Try without config file
    openssl.exe req -x509 -nodes -days 365 -newkey rsa:2048 ^
        -keyout "%SSL_DIR%\server.key" ^
        -out "%SSL_DIR%\server.crt" ^
        -subj "/C=PH/ST=Zamboanga/L=Pagadian/O=ZPPSU/OU=CICS/CN=%IP%" 2>nul
    
    if %errorLevel% neq 0 (
        echo.
        echo ERROR: Both methods failed!
        echo Please see MANUAL_CERT_GENERATION.txt for alternative
        pause
        exit /b 1
    )
)

echo Certificate generated successfully!
echo.

echo Step 3: Verifying certificate files...
if exist "%SSL_DIR%\server.crt" (
    echo [OK] server.crt created
) else (
    echo [FAIL] server.crt not found
    pause
    exit /b 1
)

if exist "%SSL_DIR%\server.key" (
    echo [OK] server.key created
) else (
    echo [FAIL] server.key not found
    pause
    exit /b 1
)
echo.

echo Step 4: Enabling SSL module in httpd.conf...
powershell -Command "(gc '%APACHE_PATH%\conf\httpd.conf') -replace '#LoadModule ssl_module', 'LoadModule ssl_module' | Out-File -encoding ASCII '%APACHE_PATH%\conf\httpd.conf'"
powershell -Command "(gc '%APACHE_PATH%\conf\httpd.conf') -replace '#LoadModule socache_shmcb_module', 'LoadModule socache_shmcb_module' | Out-File -encoding ASCII '%APACHE_PATH%\conf\httpd.conf'"
powershell -Command "(gc '%APACHE_PATH%\conf\httpd.conf') -replace '#Include conf/extra/httpd-ssl.conf', 'Include conf/extra/httpd-ssl.conf' | Out-File -encoding ASCII '%APACHE_PATH%\conf\httpd.conf'"
echo Done!
echo.

echo Step 5: Creating httpd-ssl.conf...
(
echo Listen 443
echo.
echo SSLCipherSuite HIGH:MEDIUM:!MD5:!RC4:!3DES
echo SSLProxyCipherSuite HIGH:MEDIUM:!MD5:!RC4:!3DES
echo SSLHonorCipherOrder on
echo SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
echo SSLProxyProtocol all -SSLv3 -TLSv1 -TLSv1.1
echo SSLPassPhraseDialog  builtin
echo SSLSessionCache        "shmcb:%APACHE_PATH%/logs/ssl_scache(512000)"
echo SSLSessionCacheTimeout  300
echo.
echo ^<VirtualHost _default_:443^>
echo     DocumentRoot "%WAMP_PATH%/www"
echo     ServerName %IP%:443
echo     ServerAdmin admin@localhost
echo     ErrorLog "%APACHE_PATH%/logs/ssl_error.log"
echo     TransferLog "%APACHE_PATH%/logs/ssl_access.log"
echo.
echo     SSLEngine on
echo     SSLCertificateFile "%SSL_DIR%/server.crt"
echo     SSLCertificateKeyFile "%SSL_DIR%/server.key"
echo.
echo     ^<FilesMatch "\.(cgi^|shtml^|phtml^|php)$"^>
echo         SSLOptions +StdEnvVars
echo     ^</FilesMatch^>
echo.
echo     ^<Directory "%WAMP_PATH%/www"^>
echo         Options Indexes FollowSymLinks MultiViews
echo         AllowOverride All
echo         Require all granted
echo     ^</Directory^>
echo.
echo     BrowserMatch "MSIE [2-5]" nokeepalive ssl-unclean-shutdown downgrade-1.0 force-response-1.0
echo     CustomLog "%APACHE_PATH%/logs/ssl_request.log" "%%%%t %%%%h %%%%{SSL_PROTOCOL}x %%%%{SSL_CIPHER}x \"%%%%r\" %%%%b"
echo ^</VirtualHost^>
) > "%APACHE_PATH%\conf\extra\httpd-ssl.conf"
echo Done!
echo.

echo Step 6: Opening firewall port 443...
netsh advfirewall firewall delete rule name="WAMP HTTPS" >nul 2>&1
netsh advfirewall firewall add rule name="WAMP HTTPS" dir=in action=allow protocol=TCP localport=443 >nul 2>&1
echo Done!
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Certificate files created:
echo - %SSL_DIR%\server.crt
echo - %SSL_DIR%\server.key
echo.
echo NEXT STEPS:
echo.
echo 1. RESTART WAMP SERVER NOW!
echo    - Click WAMP icon
echo    - Select "Restart All Services"
echo    - Wait for GREEN icon
echo.
echo 2. Run verify-https.bat to check setup
echo.
echo 3. Test: https://%IP%/cics-attendance-system
echo.
pause
