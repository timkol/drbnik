#arecord -t wav -f S16_LE -r48000 -c1 -D hw:0 -F0 --period-size=256 -B0 --buffer-size=4096 $1
arecord -f S16_LE -r16000 $1