#!/bin/bash
while true; do
    /usr/bin/python3 -u scripts/nwws.py
    if [[ -e "/tmp/exit_nwws" ]]; then
        echo "Process terminated normally, exiting."
        rm -f "/tmp/exit_nwws"
        exit 0
    fi
    echo "Process terminated, restarting in 3 seconds.."
    sleep 3
done
