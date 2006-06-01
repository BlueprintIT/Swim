#! /bin/sh

DIR=`dirname $0`
cd $DIR/..

if [ "$1" ]; then
	BASE=$1
else
	BASE=`date +../backups/backup-%Y%m%d-%H%M`
fi

TEMP_FILE=$BASE.tar
TARGET=$BASE.tar.gz

rm -f $TARGET
rm -f $TEMP_FILE

find site \( -name temp -o -name .svn -o -name dir.lock -o -name addons \) -prune -o -print | tar -cf $TEMP_FILE --exclude=.htaccess --no-recursion -T -
tar -rf $TEMP_FILE --ignore-failed-read bootstrap/host.conf >/dev/null 2>/dev/null

gzip $TEMP_FILE
