#!/bin/bash

EN=../app/i18n/locale/en/LC_MESSAGES/messages.po
CS=../app/i18n/locale/cs/LC_MESSAGES/messages.po
SK=../app/i18n/locale/sk/LC_MESSAGES/messages.po
HU=../app/i18n/locale/hu/LC_MESSAGES/messages.po
PL=../app/i18n/locale/pl/LC_MESSAGES/messages.po
RU=../app/i18n/locale/ru/LC_MESSAGES/messages.po

for i in $EN $CS $SK $HU $PL $RU ; do
	msgfmt $i -o ${i/.po/.mo}
done

