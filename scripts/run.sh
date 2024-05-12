#!/bin/bash
handler() {
   echo "Trapped INT signal, exiting."
   rm -f ./storage/logs/run.pid
   exit 0
}
# output PID of current script to file
echo $$ >./storage/logs/run.pid
# Trap and handle INT signal
trap handler SIGINT
# Loop forever, or until INT signal is trapped
while true; do
    # Run NWWS-OI process
    /usr/bin/python3 -u scripts/nwws.py
    PID=$!
    wait $PID
    # if /tmp/exit_nwws file exists, exit script normally
    if [[ -e "/tmp/exit_nwws" ]]; then
        echo "Process terminated normally, exiting."
        rm -f "/tmp/exit_nwws"
        exit 0
    fi
    echo "Process terminated abnormally, restarting in 3 seconds.."
    sleep 3
done
