#!/bin/bash

cp www/.htaccess.example www/.htaccess
cp app/config/config.local.neon.example app/config/config.local.neon

chmod o+w temp
chmod o+w log
