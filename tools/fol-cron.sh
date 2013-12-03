#!/bin/bash

KEY=nejaky-klic
LOG=/tmp/fol-cron.log
URL=http://fyziklani.kam.mff.cuni.cz/test/
PAUSE=30 # sec

HTTP_USER=tester
HTTP_PWD=krapet

if [ "x$HTTP_USER" != "x" ] ; then
	auth="--http-user=$HTTP_USER --http-password=$HTTP_PASSWORD"
fi


wget "$URL/cs/cron/counters?cron-key=$KEY" $auth -O - >/dev/null
sleep $PAUSE
wget "$URL/cs/cron/database?cron-key=$KEY" $auth -O - >/dev/null

date >>"$LOG"
