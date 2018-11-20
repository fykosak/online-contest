#!/bin/bash

KEY=nejaky-klic
LOG=/tmp/fol-cron.log
URL=https://online.fyziklani.cz
PAUSE=30 # sec

wget "$URL/cs/cron/counters?cron-key=$KEY" -O - >/dev/null
sleep $PAUSE
wget "$URL/cs/cron/database?freezed=1&cron-key=$KEY" -O - >/dev/null

date >>"$LOG"
