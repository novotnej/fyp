nohup vmstat 1 | (while read; do echo "$(date +%s) $REPLY"; done) >> vmstat.txt