#!/bin/bash
drbnik_path=/home/luky/fykos-org/it/drbnik/

rm -f /home/oracle/drbnik/temp/*
/usr/bin/php ${drbnik_path}bin/pdf-output/margaretky.php
rm -f /home/oracle/drbnik/temp/*

cd /home/www-tex

su - www-tex -c 'cd margaretky; vlna *.tex; pdflatex *.tex; rm -f *.aux *.log *.out *.te~; chmod og= *'
