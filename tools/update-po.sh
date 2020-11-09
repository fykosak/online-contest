#!/bin/bash

rm -rf ../app/temp/c-Nette.Template
php extract-phrases.php
EN=../app/i18n/locale/en/LC_MESSAGES/messages.po
CS=../app/i18n/locale/cs/LC_MESSAGES/messages.po
SK=../app/i18n/locale/sk/LC_MESSAGES/messages.po
HU=../app/i18n/locale/hu/LC_MESSAGES/messages.po
PL=../app/i18n/locale/pl/LC_MESSAGES/messages.po
RU=../app/i18n/locale/ru/LC_MESSAGES/messages.po
TMP=`mktemp`
POT=`mktemp`
sed 's/^msgstr.*$/msgstr ""/' $CS > $POT

msgmerge -N $EN $POT > $TMP
mv $TMP $EN

msgmerge -N $SK $POT > $TMP
mv $TMP $SK

msgmerge -N $HU $POT > $TMP
mv $TMP $HU

msgmerge -N $PL $POT > $TMP
mv $TMP $PL

msgmerge -N $RU $POT > $TMP
mv $TMP $RU
