#!/bin/bash

cp document_root/.htaccess.example document_root/.htaccess
cp app/config/config.local.neon.example app/config/config.local.neon

chmod o+w app/temp
chmod o+w app/log
