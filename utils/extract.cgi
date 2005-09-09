#! /bin/sh

echo "Content-Type: text/plain"
echo ""
echo "Extracting from backup"
cd ..
tar -xvzf site.tar.gz
echo ""
echo "Extraction complete"
