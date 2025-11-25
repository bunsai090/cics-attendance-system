# IP Address Change Guide

## When Your IP Address Changes

If your PC's IP address changes (e.g., after router restart), follow these steps:

### Step 1: Find Your New IP Address

Open Command Prompt and run:
```cmd
ipconfig
```

Look for "IPv4 Address" under your WiFi adapter.
Example: `192.168.1.10` (instead of old `192.168.1.6`)

### Step 2: Update setup-https-fixed.bat

1. Open: `setup-https-fixed.bat`
2. Find line 6:
   ```batch
   set IP=192.168.1.6
   ```
3. Change to your new IP:
   ```batch
   set IP=192.168.1.10
   ```
4. Save the file

### Step 3: Regenerate SSL Certificate

1. Right-click `setup-https-fixed.bat`
2. Select "Run as administrator"
3. Wait for "Setup Complete!" message

**Why?** SSL certificate is tied to the IP address. New IP = need new certificate.

### Step 4: Restart WAMP

1. Click WAMP icon in system tray
2. Select "Restart All Services"
3. Wait for GREEN icon

### Step 5: Update Application Config

1. Open: `backend/config/app.php`
2. Find line 10:
   ```php
   'base_url' => 'https://192.168.1.6/cics-attendance-system',
   ```
3. Change to new IP:
   ```php
   'base_url' => 'https://192.168.1.10/cics-attendance-system',
   ```
4. Save the file

### Step 6: Test

**Desktop:**
- Navigate to: `https://192.168.1.10/cics-attendance-system`
- Accept certificate warning
- Verify site loads

**Mobile:**
- Navigate to: `https://192.168.1.10/cics-attendance-system`
- Accept certificate warning
- Test time-in functionality

### Step 7: Inform Users

Tell students to use the new URL:
- Old: `https://192.168.1.6/cics-attendance-system`
- New: `https://192.168.1.10/cics-attendance-system`

---

## How to Prevent IP Changes

### Option 1: Set Static IP in Router (Recommended)

1. Access your router admin panel (usually `192.168.1.1`)
2. Find "DHCP Reservation" or "Static IP" settings
3. Reserve `192.168.1.6` for your PC's MAC address
4. Your PC will always get the same IP

### Option 2: Set Static IP in Windows

1. Open "Network and Sharing Center"
2. Click your WiFi connection
3. Click "Properties"
4. Select "Internet Protocol Version 4 (TCP/IPv4)"
5. Click "Properties"
6. Select "Use the following IP address"
7. Enter:
   - IP address: `192.168.1.6`
   - Subnet mask: `255.255.255.0`
   - Default gateway: `192.168.1.1` (your router)
   - DNS: `8.8.8.8` (Google DNS)
8. Click OK

**Recommended:** Use Option 1 (router setting) - easier and more reliable.

---

## Quick Reference

### Files to Update When IP Changes:

1. **setup-https-fixed.bat** (line 6)
   ```batch
   set IP=NEW_IP_HERE
   ```

2. **backend/config/app.php** (line 10)
   ```php
   'base_url' => 'https://NEW_IP_HERE/cics-attendance-system',
   ```

### Commands to Run:

1. Run `setup-https-fixed.bat` as Administrator
2. Restart WAMP
3. Test: `https://NEW_IP/cics-attendance-system`

---

## Troubleshooting

### "Site can't be reached" after IP change

**Problem:** Old certificate still in use

**Solution:**
1. Verify you ran `setup-https-fixed.bat` with new IP
2. Check WAMP is running (green icon)
3. Run `verify-https.bat` to check setup
4. Restart WAMP again

### Mobile still shows old IP

**Problem:** Browser cache

**Solution:**
1. Clear Chrome cache on mobile
2. Close all Chrome tabs
3. Reopen and access new URL

### Certificate warning won't accept

**Problem:** Browser remembers old certificate

**Solution:**
1. Clear browser SSL state
2. Try incognito/private mode
3. Or use different browser

---

## Summary

**When IP changes:**
1. ✅ Update `setup-https-fixed.bat` (line 6)
2. ✅ Run script as Administrator
3. ✅ Restart WAMP
4. ✅ Update `backend/config/app.php` (line 10)
5. ✅ Test on desktop and mobile

**To prevent IP changes:**
- Set static IP in router settings

**Keep these files:**
- `setup-https-fixed.bat` - For regenerating certificates
- `verify-https.bat` - For checking setup
- This guide - For reference

---

**Created**: 2025-11-25
**Purpose**: Quick reference for IP address changes
