#!/bin/bash

################################################################################
# Force Upload Assets to Server
# Use this when Git isn't updating binary files properly
################################################################################

# CONFIGURATION - UPDATE THESE
SSH_USER="u316381436"
SSH_HOST="srv540.hstgr.io"
SSH_PATH="~/domains/lightgrey-echidna-227060.hostingersite.com/public_html"

echo "🚀 Force uploading assets to server..."
echo ""

# Check if logo exists locally
if [ ! -f "public/assets/pod-logo.png" ]; then
    echo "❌ Error: public/assets/pod-logo.png not found locally!"
    exit 1
fi

echo "📊 Local logo info:"
ls -lh public/assets/pod-logo.png
file public/assets/pod-logo.png
md5 public/assets/pod-logo.png
echo ""

# Upload logo directly
echo "📤 Uploading logo to server..."
scp public/assets/pod-logo.png ${SSH_USER}@${SSH_HOST}:${SSH_PATH}/public/assets/

if [ $? -eq 0 ]; then
    echo "✅ Logo uploaded successfully!"
    echo ""
    echo "🔍 Verifying on server..."
    ssh ${SSH_USER}@${SSH_HOST} "cd ${SSH_PATH} && ls -lh public/assets/pod-logo.png && file public/assets/pod-logo.png"
    echo ""
    echo "✅ Done! Clear your browser cache:"
    echo "   Windows/Linux: Ctrl+Shift+R"
    echo "   Mac: Cmd+Shift+R"
else
    echo "❌ Upload failed!"
    exit 1
fi

