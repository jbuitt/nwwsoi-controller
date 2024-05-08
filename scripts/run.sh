#!/bin/bash
while 1; do
    /usr/bin/python -u scripts/nwws.py
    if [[ -e "/tmp/exit_nwws" ]]; then
        echo "Process terminated normally, exiting."
        exit 0
    fi
    echo "Process terminated, restarting in 3 seconds.."
    sleep 3
done
