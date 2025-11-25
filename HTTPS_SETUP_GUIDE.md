# Quick HTTPS Setup Guide for WAMP

## Automated Setup (Recommended)

1. **Right-click** `setup-https.bat` in the project root
2. Select **"Run as administrator"**
3. Follow the prompts
4. Restart WAMP
5. Update `backend/config/app.php` (see step 5 below)

---

## Manual Setup (Alternative)

### Step 1: Generate SSL Certificate

Open Command Prompt **as Administrator** and run:

```cmd
cd C:\wamp64\bin\apache\apache2.4.XX\bin
```
(Replace `apache2.4.XX` with your actual Apache version)

Generate certificate:
```cmd
openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout server.key -out server.crt
```

When prompted, enter:
- **Common Name**: Your local IP (e.g., `192.168.1.6`)
- Other fields: Press Enter to skip

### Step 2: Move Certificate Files

Create SSL directory:
```cmd
mkdir C:\wamp64\bin\apache\apache2.4.XX\conf\ssl
```

Move files:
```cmd
move server.key C:\wamp64\bin\apache\apache2.4.XX\conf\ssl\
move server.crt C:\wamp64\bin\apache\apache2.4.XX\conf\ssl\
```

### Step 3: Edit httpd.conf

Open: `C:\wamp64\bin\apache\apache2.4.XX\conf\httpd.conf`

**Uncomment** these lines (remove the `#`):
```apache
LoadModule ssl_module modules/mod_ssl.so
LoadModule socache_shmcb_module modules/mod_socache_shmcb.so
Include conf/extra/httpd-ssl.conf
```

### Step 4: Edit httpd-ssl.conf

Open: `C:\wamp64\bin\apache\apache2.4.XX\conf\extra\httpd-ssl.conf`

Replace the entire file with:

```apache
Listen 443

SSLCipherSuite HIGH:MEDIUM:!MD5:!RC4:!3DES
SSLProxyCipherSuite HIGH:MEDIUM:!MD5:!RC4:!3DES
SSLHonorCipherOrder on
SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
SSLProxyProtocol all -SSLv3 -TLSv1 -TLSv1.1
SSLPassPhraseDialog  builtin
SSLSessionCache        "shmcb:C:/wamp64/bin/apache/apache2.4.XX/logs/ssl_scache(512000)"
SSLSessionCacheTimeout  300

<VirtualHost _default_:443>
    DocumentRoot "C:/wamp64/www"
    ServerName 192.168.1.6:443
    ServerAdmin admin@localhost
    ErrorLog "C:/wamp64/bin/apache/apache2.4.XX/logs/ssl_error.log"
    TransferLog "C:/wamp64/bin/apache/apache2.4.XX/logs/ssl_access.log"

    SSLEngine on
    SSLCertificateFile "C:/wamp64/bin/apache/apache2.4.XX/conf/ssl/server.crt"
    SSLCertificateKeyFile "C:/wamp64/bin/apache/apache2.4.XX/conf/ssl/server.key"

    <FilesMatch "\.(cgi|shtml|phtml|php)$">
        SSLOptions +StdEnvVars
    </FilesMatch>

    <Directory "C:/wamp64/www">
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>

    BrowserMatch "MSIE [2-5]" nokeepalive ssl-unclean-shutdown downgrade-1.0 force-response-1.0
    CustomLog "C:/wamp64/bin/apache/apache2.4.XX/logs/ssl_request.log" "%t %h %{SSL_PROTOCOL}x %{SSL_CIPHER}x \"%r\" %b"
</VirtualHost>
```

**Important**: Replace all instances of:
- `C:/wamp64` with your WAMP path
- `apache2.4.XX` with your Apache version
- `192.168.1.6` with your local IP address

### Step 5: Update Application Config

Edit: `backend/config/app.php`

Change:
```php
'base_url' => 'https://192.168.1.6/cics-attendance-system', // Change to HTTPS
'session' => [
    'lifetime' => 7200,
    'name' => 'cics_session',
    'secure' => true, // Change to true
    'httponly' => true,
],
```

### Step 6: Open Firewall Port

Open Command Prompt **as Administrator**:

```cmd
netsh advfirewall firewall add rule name="WAMP HTTPS" dir=in action=allow protocol=TCP localport=443
```

### Step 7: Restart WAMP

1. Click WAMP icon in system tray
2. Select **"Restart All Services"**
3. Wait for services to restart (icon should turn green)

---

## Testing

### On Desktop
1. Open browser
2. Navigate to: `https://192.168.1.6/cics-attendance-system`
3. You'll see a security warning (normal for self-signed certificates)
4. Click **"Advanced"** → **"Proceed to 192.168.1.6"**
5. Login and test

### On Mobile (Android)
1. Ensure mobile is on the **same WiFi network**
2. Open Chrome
3. Navigate to: `https://192.168.1.6/cics-attendance-system`
4. Tap **"Advanced"** → **"Proceed to 192.168.1.6 (unsafe)"**
5. Login
6. When clicking Time-In, you should see **location permission prompt**
7. Grant permission
8. Time-in should work!

---

## Troubleshooting

### Apache won't start after changes
- Check Apache error log: `C:\wamp64\bin\apache\apache2.4.XX\logs\error.log`
- Verify all paths in `httpd-ssl.conf` are correct
- Ensure port 443 is not used by another application

### Certificate error persists
- Clear browser cache
- Try incognito/private mode
- Verify certificate files exist in `conf/ssl/` directory

### Mobile still can't access
- Verify firewall rule is active
- Check that mobile and PC are on same network
- Try accessing from mobile browser: `https://192.168.1.6` (without the path)

### Location still denied on mobile
- Ensure you're using `https://` (not `http://`)
- Check that location services are enabled on device
- Try clearing Chrome data on mobile
- Verify GPS is enabled

---

## Reverting Changes

If you need to revert to HTTP:

1. Edit `httpd.conf` - comment out (add `#` before):
   ```apache
   #Include conf/extra/httpd-ssl.conf
   ```

2. Edit `backend/config/app.php`:
   ```php
   'base_url' => 'http://192.168.1.6/cics-attendance-system',
   'session' => [
       'secure' => false,
   ],
   ```

3. Restart WAMP

---

## Production Deployment

For production, **DO NOT** use self-signed certificates. Instead:

1. Get a domain name (e.g., `attendance.zppsu.edu.ph`)
2. Use **Let's Encrypt** for free SSL certificate
3. Or purchase commercial SSL certificate
4. Configure proper HTTPS on production server
5. Update `base_url` in config

---

## Need Help?

- Check WAMP documentation: https://wampserver.aviatechno.net/
- Apache SSL guide: https://httpd.apache.org/docs/2.4/ssl/
- Let's Encrypt: https://letsencrypt.org/
