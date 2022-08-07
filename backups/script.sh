#!/bin/bash

PATH=`dirname $0`
HOUR=`/bin/date +%H`
HOURLYBACKUP="$PATH/$HOUR"_backup.sql.gz
DATE=`/bin/date +%Y%m%d`
DAILYBACKUP="$PATH/$DATE"_backup.sql.gz

/usr/bin/mysqldump -u fastaval2017 -pbohWiex1ohtuphue -h db infosys2017 | /bin/gzip > $HOURLYBACKUP

if [ "$HOUR" = "00" ]
then
    /usr/bin/mysqldump -u fastaval2017 -pbohWiex1ohtuphue -h db infosys2017 | /bin/gzip > $DAILYBACKUP
    /usr/bin/find $PATH -name '*_backup.sql.gz' -mtime +30 -exec /bin/rm -f '{}' \;
fi
