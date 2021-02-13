#!/bin/bash

KEY=nejaky-klic
LOG=/tmp/fol-cron.log
URL=https://online.fyziklani.cz
PAUSE=30 # sec
# TODO !!!!
wget "$URL/cs/cron/counters?cron-key=$KEY" -O - >/dev/null
sleep $PAUSE
wget "$URL/cs/cron/database?cron-key=$KEY" -O - >/dev/null

date >>"$LOG"
