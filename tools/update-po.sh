#!/bin/bash

rm -rf ../app/temp/c-Nette.Template
EN=../app/i18n/locale/en/LC_MESSAGES/messages.po
CS=../app/i18n/locale/cs/LC_MESSAGES/messages.po
TMP=`mktemp`
POT=`mktemp`
CSDEF=`mktemp`
cat $CS > $CSDEF

php extract-phrases.php
sed 's/^msgstr.*$/msgstr ""/' $CS > $POT

msgmerge -N $EN $POT > $TMP
mv $TMP $EN

msgmerge -N $CSDEF $POT > $CS
