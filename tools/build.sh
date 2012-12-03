#!/bin/bash

EN=../app/i18n/locale/en/LC_MESSAGES/messages.po
CS=../app/i18n/locale/cs/LC_MESSAGES/messages.po

for i in $EN $CS ; do
	msgfmt $i -o ${i/.po/.mo}
done

