# Manual Certificate Generation Guide

## Problem

OpenSSL in WAMP is looking for config file in wrong location:
- Looking for: `C:\Apache24\conf\openssl.cnf`
- Should be: `D:\Programming\wamp\bin\apache\apache2.4.62.1\conf\openssl.cnf`

## Solution 1: Use Fixed Script (Recommended)

Run `setup-https-fixed.bat` as Administrator. This script:
- Sets correct OpenSSL config path
- Has fallback if config not found
- Verifies certificate creation

## Solution 2: Manual Certificate Generation

If the script still fails, generate certificates manually:

### Step 1: Open PowerShell as Administrator

Right-click Start → Windows PowerShell (Admin)

### Step 2: Navigate to Apache bin directory

```powershell
cd D:\Programming\wamp\bin\apache\apache2.4.62.1\bin
```

### Step 3: Set OpenSSL config path

```powershell
$env:OPENSSL_CONF = "D:\Programming\wamp\bin\apache\apache2.4.62.1\conf\openssl.cnf"
```

### Step 4: Create SSL directory

```powershell
New-Item -ItemType Directory -Force -Path "D:\Programming\wamp\bin\apache\apache2.4.62.1\conf\ssl"
```

### Step 5: Generate certificate

```powershell
.\openssl.exe req -x509 -nodes -days 365 -newkey rsa:2048 `
    -keyout "..\conf\ssl\server.key" `
    -out "..\conf\ssl\server.crt" `
    -subj "/C=PH/ST=Zamboanga/L=Pagadian/O=ZPPSU/OU=CICS/CN=192.168.1.6"
```

### Step 6: Verify files created

```powershell
dir ..\conf\ssl\
```

You should see:
- server.crt
- server.key

### Step 7: Continue with Apache configuration

Run these commands in PowerShell (as Admin):

```powershell
# Enable SSL module
$httpdConf = "D:\Programming\wamp\bin\apache\apache2.4.62.1\conf\httpd.conf"
(Get-Content $httpdConf) -replace '#LoadModule ssl_module', 'LoadModule ssl_module' | Set-Content $httpdConf
(Get-Content $httpdConf) -replace '#LoadModule socache_shmcb_module', 'LoadModule socache_shmcb_module' | Set-Content $httpdConf
(Get-Content $httpdConf) -replace '#Include conf/extra/httpd-ssl.conf', 'Include conf/extra/httpd-ssl.conf' | Set-Content $httpdConf
```

### Step 8: Create httpd-ssl.conf

Copy this content to: `D:\Programming\wamp\bin\apache\apache2.4.62.1\conf\extra\httpd-ssl.conf`

```apache
Listen 443

SSLCipherSuite HIGH:MEDIUM:!MD5:!RC4:!3DES
SSLProxyCipherSuite HIGH:MEDIUM:!MD5:!RC4:!3DES
SSLHonorCipherOrder on
SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
SSLProxyProtocol all -SSLv3 -TLSv1 -TLSv1.1
SSLPassPhraseDialog  builtin
SSLSessionCache        "shmcb:D:/Programming/wamp/bin/apache/apache2.4.62.1/logs/ssl_scache(512000)"
SSLSessionCacheTimeout  300

<VirtualHost _default_:443>
    DocumentRoot "D:/Programming/wamp/www"
    ServerName 192.168.1.6:443
    ServerAdmin admin@localhost
    ErrorLog "D:/Programming/wamp/bin/apache/apache2.4.62.1/logs/ssl_error.log"
    TransferLog "D:/Programming/wamp/bin/apache/apache2.4.62.1/logs/ssl_access.log"

    SSLEngine on
    SSLCertificateFile "D:/Programming/wamp/bin/apache/apache2.4.62.1/conf/ssl/server.crt"
    SSLCertificateKeyFile "D:/Programming/wamp/bin/apache/apache2.4.62.1/conf/ssl/server.key"

    <FilesMatch "\.(cgi|shtml|phtml|php)$">
        SSLOptions +StdEnvVars
    </FilesMatch>

    <Directory "D:/Programming/wamp/www">
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>

    BrowserMatch "MSIE [2-5]" nokeepalive ssl-unclean-shutdown downgrade-1.0 force-response-1.0
    CustomLog "D:/Programming/wamp/bin/apache/apache2.4.62.1/logs/ssl_request.log" "%t %h %{SSL_PROTOCOL}x %{SSL_CIPHER}x \"%r\" %b"
</VirtualHost>
```

### Step 9: Open firewall

```powershell
netsh advfirewall firewall add rule name="WAMP HTTPS" dir=in action=allow protocol=TCP localport=443
```

### Step 10: Restart WAMP

- Click WAMP icon → Restart All Services
- Wait for GREEN icon

### Step 11: Test

Desktop browser: `https://192.168.1.6/cics-attendance-system`

## Solution 3: Use Pre-Generated Certificates

If OpenSSL continues to fail, I can provide you with pre-generated certificate files that you can just copy into place.

## Verification

After any method, run `verify-https.bat` to check if everything is set up correctly.

All checks should show [OK]:
- [OK] SSL directory exists
- [OK] server.crt exists
- [OK] server.key exists
- [OK] httpd-ssl.conf exists
- [OK] Apache is listening on port 443
- [OK] Firewall rule exists

## Troubleshooting

### OpenSSL not found
Check if file exists:
```
D:\Programming\wamp\bin\apache\apache2.4.62.1\bin\openssl.exe
```

### Config file not found
Check if file exists:
```
D:\Programming\wamp\bin\apache\apache2.4.62.1\conf\openssl.cnf
```

If not, the fallback method (without config) should work.

### Apache won't start after changes
Check error log:
```
D:\Programming\wamp\bin\apache\apache2.4.62.1\logs\error.log
```

Common issues:
- Syntax error in httpd-ssl.conf
- Port 443 already in use
- Certificate files not found

## Need Help?

If you're still stuck:
1. Tell me which step failed
2. Share any error messages
3. Check if certificate files were created
4. Check WAMP icon color after restart

I'll help you debug further!
