#! /bin/sh

echo "Content-Type: text/html"
echo ""
cd ..
echo "<p>Creating backup...</p>"
rm -f site.tar.gz
rm -f site.tar
echo "<p>Backing up user site...</p>"
find containers/user \( -name temp -o -name .svn -o -name dir.lock \) -prune -o -print | tar -cf site.tar --no-recursion -T -
echo "<p>Backing up config...</p>"
tar -rf site.tar bootstrap/host.conf conf
gzip site.tar
echo "<p>Backup complete: <a href=\"../site.tar.gz\">Download</a></p>"
