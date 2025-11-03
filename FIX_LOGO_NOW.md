# 🔥 IMMEDIATE FIX FOR LOGO ISSUE

The logo file exists in Git but the server has an old version. Here's how to fix it **RIGHT NOW**.

---

## Option 1: Upload Via SSH (FASTEST)

### Step 1: SSH into your server
```bash
ssh u316381436@srv540.hstgr.io
```

### Step 2: Navigate to your site
```bash
cd ~/domains/lightgrey-echidna-227060.hostingersite.com/public_html
```

### Step 3: Check current logo
```bash
ls -lh public/assets/pod-logo.png
file public/assets/pod-logo.png
```

**If it shows a DIFFERENT size than 44K or says "ASCII text" or "SVG", it's the WRONG file.**

### Step 4: Delete the old logo
```bash
rm public/assets/pod-logo.png
```

### Step 5: Pull from Git again
```bash
git checkout public/assets/pod-logo.png
```

### Step 6: Verify it's correct now
```bash
ls -lh public/assets/pod-logo.png
file public/assets/pod-logo.png
```

**Should show:** `PNG image data, 2501 x 1281` and size around `44K`

---

## Option 2: Upload Directly via FTP/File Manager

### Step 1: Open Hostinger File Manager
1. Go to Hostinger panel
2. Open File Manager
3. Navigate to: `domains/lightgrey-echidna-227060.hostingersite.com/public_html/public/assets/`

### Step 2: Delete old logo
Delete the existing `pod-logo.png` file

### Step 3: Upload new logo
Upload the `pod-logo.png` file from your local `public/assets/` folder

---

## Option 3: Use the Upload Script (From Your Local Machine)

Run this command from your local project directory:

```bash
./deployment/force-upload-assets.sh
```

**Note:** You might need to enter your SSH password.

---

## Option 4: Direct SCP Command (From Your Local Machine)

```bash
scp public/assets/pod-logo.png u316381436@srv540.hstgr.io:~/domains/lightgrey-echidna-227060.hostingersite.com/public_html/public/assets/
```

---

## After Uploading - Verify

### Check the file on server:
```bash
ssh u316381436@srv540.hstgr.io "cd ~/domains/lightgrey-echidna-227060.hostingersite.com/public_html && file public/assets/pod-logo.png && ls -lh public/assets/pod-logo.png"
```

**Expected output:**
```
public/assets/pod-logo.png: PNG image data, 2501 x 1281, 8-bit/color RGBA, non-interlaced
-rw-r--r-- 1 u316381436 u316381436 44K Nov  3 XX:XX public/assets/pod-logo.png
```

---

## Clear Browser Cache

After fixing the file on the server, you **MUST** clear your browser cache:

### Hard Refresh:
- **Windows/Linux:** `Ctrl + Shift + R` or `Ctrl + F5`
- **Mac:** `Cmd + Shift + R`

### Or use Incognito/Private Window:
- Open your site in a new Incognito/Private window
- If the logo shows correctly there, it's a cache issue

---

## Why This Happened

Git sometimes doesn't properly update binary files (images) during `git pull`, especially on shared hosting. The file exists in the repository, but the server's copy is outdated.

**The solution:** Manually replace the file once, then it should stay correct.

---

## Quick Diagnosis

Run this on your server to see what's wrong:

```bash
ssh u316381436@srv540.hstgr.io "cd ~/domains/lightgrey-echidna-227060.hostingersite.com/public_html && echo '=== Current logo info ===' && file public/assets/pod-logo.png && ls -lh public/assets/pod-logo.png && echo '' && echo '=== Git status ===' && git status public/assets/pod-logo.png"
```

This will show you:
- What type of file is currently on the server
- Its size
- If Git knows about any changes

---

**The logo file is correct in Git. The server just has an old cached version. Delete and re-pull or upload directly to fix.**

