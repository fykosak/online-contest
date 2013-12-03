#!/bin/bash

cp document_root/.htaccess.example document_root/.htaccess
cp app/config.local.ini.example app/config.local.ini

chmod o+w app/temp
chmod o+w app/log
