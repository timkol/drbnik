arecord -f S16_LE -r48000 -c2 -D hw:0 -F0 --period-size=256 -B0 --buffer-size=4096 | aplay -D hw:0 -
