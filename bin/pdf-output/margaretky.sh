#!/bin/bash
drbnik_path=/home/luky/fykos-org/it/drbnik/

rm -f ${drbnik_path}temp/*
/usr/bin/php ${drbnik_path}bin/pdf-output/margaretky.php
rm -f ${drbnik_path}temp/*

cd /home/www-tex

su - www-tex -c 'cd margaretky; vlna *.tex; pdflatex *.tex; rm -f *.aux *.log *.out *.te~; chmod og= *'
