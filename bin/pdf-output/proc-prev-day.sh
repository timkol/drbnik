#!/bin/bash
drbnik_path=/home/drbnikd/drbnik/

rm -rf ${drbnik_path}temp/*
filename=`/usr/bin/php ${drbnik_path}bin/pdf-output/proc-prev-day.php`
rm -rf ${drbnik_path}temp/*
cd /home/www-tex
echo $filename
su - www-tex -c 'cd drby; vlna drb'$filename'.tex; pdflatex drb'$filename'.tex; rm -f *.aux *.log *.out *.te~; chmod og= *'
su - www-tex -c 'cd statistika-autori; vlna autor-stat'$filename'.tex; pdflatex autor-stat'$filename'.tex; rm -f *.aux *.log *.out *.te~; chmod og= *'
